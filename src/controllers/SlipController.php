<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Slip;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Intervention;

class SlipController
{
    private Slip $model;
    private Patient $patientModel;
    private Doctor $doctorModel;
    private Intervention $interventionModel;

    public function __construct()
    {
        AuthController::requireLogin();
        $this->model = new Slip();
        $this->patientModel = new Patient();
        $this->doctorModel = new Doctor();
        $this->interventionModel = new Intervention();
    }

    public function index(): void
    {
        // Utilisation de la nouvelle signature avec 6 paramètres
        $slips = $this->model->getAllFiltered(null, null, null, null, null, false);
        $patients = $this->patientModel->getAll();
        $doctors = $this->doctorModel->getAll();
        $interventions = $this->interventionModel->getAll();
        include __DIR__ . '/../views/SlipView.php';
    }

    public function create(): void
    {
        $slip = null;
        $lines = [];
        $slips = $this->model->getAllFiltered(null, null, null, null, null, false);
        $patients = $this->patientModel->getAll();
        $doctors = $this->doctorModel->getAll();
        $interventions = $this->interventionModel->getAll();
        include __DIR__ . '/../views/SlipView.php';
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $slip = $this->model->getById($id);
        
        if (!$slip) {
            $_SESSION['error'] = "Bulletin non trouvé";
            header('Location: index.php?controller=slip&action=index');
            exit;
        }
        
        $lines = $this->model->getLines($id);
        $slips = $this->model->getAllFiltered(null, null, null, null, null, false);
        $patients = $this->patientModel->getAll();
        $doctors = $this->doctorModel->getAll();
        $interventions = $this->interventionModel->getAll();
        include __DIR__ . '/../views/SlipView.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lines_data = [];
            $total = 0.0;
            $upload_count = 0;
            
            // Configuration du dossier d'upload
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Debug upload
            error_log("=== UPLOAD DEBUG START ===");
            error_log("POST lines count: " . count($_POST['lines'] ?? []));
            error_log("FILES exists: " . (isset($_FILES['lines']) ? 'YES' : 'NO'));
            
            if (isset($_FILES['lines'])) {
                foreach ($_FILES['lines']['name'] as $idx => $names) {
                    $file_name = $names['fichier'] ?? 'NO_FILE';
                    $file_error = $_FILES['lines']['error'][$idx]['fichier'] ?? 'NO_ERROR';
                    error_log("Line $idx: file=$file_name, error=$file_error");
                }
            }
            error_log("=== UPLOAD DEBUG END ===");
            
            // === TRAITEMENT DES LIGNES ET FICHIERS ===
            if (isset($_POST['lines']) && is_array($_POST['lines'])) {
                foreach ($_POST['lines'] as $i => $line) {
                    // Vérifier que la ligne a un type d'intervention
                    if (empty($line['intervention_type_id'])) {
                        error_log("Line $i: SKIP - no intervention_type_id");
                        continue;
                    }
                    
                    // Récupérer et valider le montant
                    $montant = (float)str_replace(',', '.', $line['montant']);
                    $total += $montant;
                    
                    $file_path = null;
                    
                    // === TRAITEMENT DU FICHIER POUR CETTE LIGNE ===
                    if (isset($_FILES['lines']) && 
                        isset($_FILES['lines']['error'][$i]['fichier'])) {
                        
                        $file_error = $_FILES['lines']['error'][$i]['fichier'];
                        $file_name = $_FILES['lines']['name'][$i]['fichier'] ?? '';
                        
                        error_log("Line $i: Processing file - name=$file_name, error=$file_error");
                        
                        // UPLOAD_ERR_OK = 0 signifie upload réussi
                        if ($file_error === UPLOAD_ERR_OK) {
                            if (!empty($file_name)) {
                                $file_tmp = $_FILES['lines']['tmp_name'][$i]['fichier'];
                                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                                $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
                                
                                if (in_array($file_ext, $allowed_extensions)) {
                                    // === NOM UNIQUE AVEC INDEX POUR ÉVITER ÉCRASEMENT ===
                                    $new_file_name = 'doc_' . uniqid() . '_' . time() . '_' . $i . '.' . $file_ext;
                                    $destination = $upload_dir . $new_file_name;
                                    
                                    if (move_uploaded_file($file_tmp, $destination)) {
                                        $file_path = $new_file_name;
                                        $upload_count++;
                                        error_log("Line $i: File uploaded successfully - $new_file_name");
                                    } else {
                                        error_log("Line $i: move_uploaded_file FAILED - $new_file_name");
                                    }
                                } else {
                                    error_log("Line $i: Extension not allowed - $file_ext");
                                }
                            } else {
                                error_log("Line $i: Empty file name");
                            }
                        } else {
                            error_log("Line $i: Upload error code - $file_error");
                        }
                    } else {
                        error_log("Line $i: No file in \$_FILES or error key missing");
                    }
                    // === FIN TRAITEMENT FICHIER ===
                    
                    // Ajouter la ligne avec SON propre fichier
                    $lines_data[] = [
                        'intervention_type_id' => (int)$line['intervention_type_id'],
                        'montant' => $montant,
                        'fichier_path' => $file_path
                    ];
                    
                    error_log("Line $i: Added to lines_data with fichier_path=" . ($file_path ?? 'NULL'));
                }
            }
            
            error_log("Total lines processed: " . count($lines_data));
            error_log("Total files uploaded: $upload_count");
            error_log("=== END STORE ===");

            // Validation : Au moins une ligne requise
            if (empty($lines_data)) {
                $_SESSION['error'] = "Au moins une ligne d'intervention est requise";
                header('Location: index.php?controller=slip&action=create');
                exit;
            }

            // Récupérer et valider le numéro de bulletin
            $numero_bulletin = isset($_POST['numero_bulletin']) ? trim($_POST['numero_bulletin']) : '';
            
            if (empty($numero_bulletin)) {
                $_SESSION['error'] = "Le numéro de bulletin est obligatoire";
                header('Location: index.php?controller=slip&action=create');
                exit;
            }

            // Valider patient et médecin
            $patient_id = isset($_POST['patient_id']) ? (int)$_POST['patient_id'] : 0;
            $doctor_id = isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : 0;
            
            if ($patient_id <= 0) {
                $_SESSION['error'] = "Le patient est obligatoire";
                header('Location: index.php?controller=slip&action=create');
                exit;
            }
            
            if ($doctor_id <= 0) {
                $_SESSION['error'] = "Le médecin est obligatoire";
                header('Location: index.php?controller=slip&action=create');
                exit;
            }

            // Valider la date des soins
            $date_soins = $_POST['date_soins'] ?? '';
            if (empty($date_soins)) {
                $_SESSION['error'] = "La date des soins est obligatoire";
                header('Location: index.php?controller=slip&action=create');
                exit;
            }

            // Le montant déboursé est égal au total des lignes
            $montant_debourse = $total;
            $montant_rembourse = isset($_POST['montant_rembourse']) ? (float)str_replace(',', '.', $_POST['montant_rembourse']) : 0.0;

            // === CRÉER $data AVANT le if/else ===
            $data = [
                'numero_bulletin' => $numero_bulletin,
                'patient_id' => $patient_id,
                'doctor_id' => $doctor_id,
                'date_soins' => $date_soins,
                'date_remboursement' => $_POST['date_remboursement'] ?: null,
                'commentaire' => htmlspecialchars($_POST['commentaire'] ?? ''),
                'total' => $total,
                'montant_debourse' => $montant_debourse,
                'montant_rembourse' => $montant_rembourse,
                'lines' => $lines_data
            ];

            try {
                // === MODIFICATION : Conserver les anciens fichiers par INDEX ===
                if (isset($_POST['id']) && (int)$_POST['id'] > 0) {
                    $old_lines = $this->model->getLines((int)$_POST['id']);
                    
                    // Créer un tableau des anciens fichiers par INDEX de ligne
                    $existing_files = [];
                    foreach ($old_lines as $idx => $old_line) {
                        if (!empty($old_line['fichier_path'])) {
                            $existing_files[$idx] = $old_line['fichier_path'];
                        }
                    }
                    
                    // Conserver les anciens fichiers si aucun nouveau fichier n'est uploadé pour cette ligne
                    foreach ($lines_data as $idx => &$line_data) {
                        if (empty($line_data['fichier_path']) && 
                            isset($existing_files[$idx])) {
                            $line_data['fichier_path'] = $existing_files[$idx];
                            error_log("Line $idx: Keeping old file - " . $existing_files[$idx]);
                        }
                    }
                    
                    // Mettre à jour $data['lines'] avec les fichiers conservés
                    $data['lines'] = $lines_data;
                    
                    $this->model->update((int)$_POST['id'], $data);
                    $_SESSION['success'] = "Bulletin modifié avec succès - $upload_count nouveau(x) fichier(s)";
                } else {
                    // Création : vérifier l'unicité du numéro de bulletin
                    if ($this->model->exists($numero_bulletin)) {
                        $_SESSION['error'] = "Ce numéro de bulletin existe déjà";
                        header('Location: index.php?controller=slip&action=create');
                        exit;
                    }
                    $this->model->create($data);
                    $_SESSION['success'] = "Bulletin créé avec succès - $upload_count fichier(s) uploadé(s)";
                }
            } catch (\Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
                header('Location: index.php?controller=slip&action=create');
                exit;
            }
        }
        header('Location: index.php?controller=slip&action=index');
        exit;
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $slip = $this->model->getById($id);
            if ($slip) {
                // Supprimer les fichiers associés
                $lines = $this->model->getLines($id);
                $upload_dir = __DIR__ . '/../uploads/';
                foreach ($lines as $line) {
                    if (!empty($line['fichier_path']) && file_exists($upload_dir . $line['fichier_path'])) {
                        unlink($upload_dir . $line['fichier_path']);
                    }
                }
                
                $this->model->delete($id);
                $_SESSION['success'] = "Bulletin N° " . htmlspecialchars($slip['numero_bulletin']) . " supprimé avec succès";
            } else {
                $_SESSION['error'] = "Bulletin non trouvé";
            }
        }
        header('Location: index.php?controller=slip&action=index');
        exit;
    }

    public function print(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $slip = $this->model->getById($id);
        
        if (!$slip) {
            $_SESSION['error'] = "Bulletin non trouvé";
            header('Location: index.php?controller=slip&action=index');
            exit;
        }
        
        $lines = $this->model->getLines($id);
        $slips = [];
        $patients = [];
        $doctors = [];
        $interventions = [];
        include __DIR__ . '/../views/SlipView.php';
        exit;
    }

    public function viewFile(): void
    {
        $file = $_GET['file'] ?? '';
        
        if (empty($file)) {
            http_response_code(404);
            echo "Fichier non trouvé";
            exit;
        }
        
        // Sécurité : empêcher les attaques par chemin relatif
        $file = basename($file);
        $upload_dir = __DIR__ . '/../uploads/';
        $file_path = $upload_dir . $file;
        
        if (!file_exists($file_path)) {
            http_response_code(404);
            echo "Fichier non trouvé : " . htmlspecialchars($file);
            exit;
        }
        
        $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime_types = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        
        $mime_type = $mime_types[$file_ext] ?? 'application/octet-stream';
        
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . filesize($file_path));
        header('Content-Disposition: inline; filename="' . $file . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        
        readfile($file_path);
        exit;
    }

    public function exportCsv(): void
    {
        // Vérifier l'authentification
        AuthController::requireLogin();
        
        // Récupérer les bulletins (avec la nouvelle signature à 6 paramètres)
        $slips = $this->model->getAllFiltered(null, null, null, null, null, false);
        
        // Vérifier qu'il y a des données
        if (empty($slips)) {
            $_SESSION['error'] = "Aucune donnée à exporter";
            header('Location: index.php?controller=dashboard&action=index');
            exit;
        }
        
        // Nom du fichier avec date et heure
        $filename = 'bulletins_soins_' . date('Y-m-d_H-i-s') . '.csv';
        
        // === EN-TÊTES HTTP (DOIVENT ÊTRE AVANT TOUT OUTPUT) ===
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        // Ouvrir la sortie
        $output = fopen('php://output', 'w');
        
        // Ajouter le BOM pour Excel (reconnaissance UTF-8)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // En-têtes de colonnes (séparateur point-virgule pour Excel français)
        fputcsv($output, [
            'N° Bulletin',
            'Patient',
            'Médecin',
            'Date Soins',
            'Date Remboursement',
            'Total (TND)',
            'Déboursé (TND)',
            'Remboursé (TND)',
            'Solde (TND)'
        ], ';');
        
        // Données
        foreach ($slips as $slip) {
            $solde = (float)$slip['montant_debourse'] - (float)$slip['montant_rembourse'];
            
            // Formater les dates pour Excel (JJ/MM/AAAA)
            $date_soins = !empty($slip['date_soins']) ? date('d/m/Y', strtotime($slip['date_soins'])) : '';
            $date_remb = !empty($slip['date_remboursement']) ? date('d/m/Y', strtotime($slip['date_remboursement'])) : '';
            
            fputcsv($output, [
                $slip['numero_bulletin'],
                $slip['patient_nom'],
                $slip['doctor_nom'],
                $date_soins,
                $date_remb,
                str_replace('.', ',', number_format((float)$slip['total'], 3, '.', '')),
                str_replace('.', ',', number_format((float)$slip['montant_debourse'], 3, '.', '')),
                str_replace('.', ',', number_format((float)$slip['montant_rembourse'], 3, '.', '')),
                str_replace('.', ',', number_format($solde, 3, '.', ''))
            ], ';');
        }
        
        fclose($output);
        exit;
    }

    public function exportPdf(): void
    {
        $slips = $this->model->getAllFiltered(null, null, null, null, null, false);
        $patients = $this->patientModel->getAll();
        $doctors = $this->doctorModel->getAll();
        $interventions = $this->interventionModel->getAll();
        include __DIR__ . '/../views/SlipView.php';
        exit;
    }
}