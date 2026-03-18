<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Slip;
use App\Models\Patient;
use App\Models\Doctor;

class DashboardController
{
    private Slip $slipModel;
    private Patient $patientModel;
    private Doctor $doctorModel;

    public function __construct()
    {
        error_log("=== DASHBOARD CONSTRUCT CALLED ===");
        AuthController::requireLogin();
        $this->slipModel = new Slip();
        $this->patientModel = new Patient();
        $this->doctorModel = new Doctor();
        error_log("=== DASHBOARD CONSTRUCT DONE ===");
    }

    public function index(): void
    {
        error_log("=== DASHBOARD INDEX CALLED ===");
        
        $date_debut = $_GET['date_debut'] ?? null;
        $date_fin = $_GET['date_fin'] ?? null;
        $doctor_id = isset($_GET['doctor_id']) && $_GET['doctor_id'] !== '' ? (int)$_GET['doctor_id'] : null;
        $patient_id = isset($_GET['patient_id']) && $_GET['patient_id'] !== '' ? (int)$_GET['patient_id'] : null;
        $search = $_GET['search'] ?? null;
        $non_rembourses = isset($_GET['non_rembourses']) && $_GET['non_rembourses'] === '1';

        $slips = $this->slipModel->getAllFiltered($date_debut, $date_fin, $doctor_id, $patient_id, $search, $non_rembourses);
        $patients = $this->patientModel->getAll();
        $doctors = $this->doctorModel->getAll();

        error_log("=== DASHBOARD INDEX DONE - " . count($slips) . " slips ===");
        include __DIR__ . '/../views/DashboardView.php';
    }

    public function exportCsv(): void
    {
        error_log("=== EXPORT CSV METHOD STARTED ===");
        error_log("PHP Version: " . phpversion());
        error_log("Session status: " . session_status());
        error_log("Session ID: " . session_id());
        error_log("Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));
        error_log("Session username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'NOT SET'));
        error_log("GET params: " . print_r($_GET, true));
        error_log("SERVER REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'NOT SET'));
        
        // Vérifier l'authentification MANUELLEMENT
        if (!isset($_SESSION['user_id'])) {
            error_log("EXPORT CSV: AUTH FAILED - No user_id in session");
            error_log("EXPORT CSV: Redirecting to login");
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        error_log("EXPORT CSV: AUTH PASSED - user_id=" . $_SESSION['user_id']);
        
        // === RÉCUPÉRER LES FILTRES ===
        $date_debut = $_GET['date_debut'] ?? null;
        $date_fin = $_GET['date_fin'] ?? null;
        $doctor_id = isset($_GET['doctor_id']) && $_GET['doctor_id'] !== '' ? (int)$_GET['doctor_id'] : null;
        $patient_id = isset($_GET['patient_id']) && $_GET['patient_id'] !== '' ? (int)$_GET['patient_id'] : null;
        $search = $_GET['search'] ?? null;
        $non_rembourses = isset($_GET['non_rembourses']) && $_GET['non_rembourses'] === '1';

        error_log("EXPORT CSV: Filters - debut=$date_debut, fin=$date_fin, non_rembourses=" . ($non_rembourses ? '1' : '0'));
        
        // === APPEL AU MODÈLE ===
        error_log("EXPORT CSV: Calling getAllFiltered...");
        try {
            $slips = $this->slipModel->getAllFiltered($date_debut, $date_fin, $doctor_id, $patient_id, $search, $non_rembourses);
            error_log("EXPORT CSV: getAllFiltered returned " . count($slips) . " slips");
        } catch (\Exception $e) {
            error_log("EXPORT CSV: Exception in getAllFiltered: " . $e->getMessage());
            error_log("EXPORT CSV: Stack trace: " . $e->getTraceAsString());
            exit;
        }

        // Vérifier les données
        if (empty($slips)) {
            error_log("EXPORT CSV: No slips to export");
            $_SESSION['error'] = "Aucune donnée à exporter";
            header('Location: index.php?controller=dashboard&action=index');
            exit;
        }

        // Nom du fichier
        $filename = 'bulletins_soins_' . date('Y-m-d_H-i-s') . '.csv';
        error_log("EXPORT CSV: Filename = $filename");

        // === HEADERS HTTP ===
        error_log("EXPORT CSV: Sending HTTP headers...");
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        error_log("EXPORT CSV: Headers sent");

        // === ÉCRITURE CSV ===
        error_log("EXPORT CSV: Opening output stream...");
        $output = fopen('php://output', 'w');
        if (!$output) {
            error_log("EXPORT CSV: ERROR - Failed to open php://output");
            exit;
        }
        
        // BOM UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        error_log("EXPORT CSV: BOM written");
        
        // En-têtes
        $headers = ['N° Bulletin', 'Patient', 'Médecin', 'Date Soins', 'Date Remboursement', 'Total (TND)', 'Déboursé (TND)', 'Remboursé (TND)', 'Différence (TND)'];
        fputcsv($output, $headers, ';');
        error_log("EXPORT CSV: Column headers written");
        
        // Données
        $count = 0;
        foreach ($slips as $slip) {
            $solde = (float)$slip['montant_debourse'] - (float)$slip['montant_rembourse'];
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
            $count++;
        }
        error_log("EXPORT CSV: $count data rows written");
        
        fclose($output);
        error_log("EXPORT CSV: Output stream closed");
        error_log("=== EXPORT CSV METHOD COMPLETED ===");
        
        exit;
    }

    public function exportPdf(): void
    {
        error_log("=== EXPORT PDF CALLED ===");
        
        AuthController::requireLogin();
        
        $date_debut = $_GET['date_debut'] ?? null;
        $date_fin = $_GET['date_fin'] ?? null;
        $doctor_id = isset($_GET['doctor_id']) && $_GET['doctor_id'] !== '' ? (int)$_GET['doctor_id'] : null;
        $patient_id = isset($_GET['patient_id']) && $_GET['patient_id'] !== '' ? (int)$_GET['patient_id'] : null;
        $search = $_GET['search'] ?? null;
        $non_rembourses = isset($_GET['non_rembourses']) && $_GET['non_rembourses'] === '1';

        $slips = $this->slipModel->getAllFiltered($date_debut, $date_fin, $doctor_id, $patient_id, $search, $non_rembourses);
        $patients = $this->patientModel->getAll();
        $doctors = $this->doctorModel->getAll();
        
        include __DIR__ . '/../views/DashboardView.php';
        exit;
    }
}