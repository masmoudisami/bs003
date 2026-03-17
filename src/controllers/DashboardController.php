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
        AuthController::requireLogin();
        $this->slipModel = new Slip();
        $this->patientModel = new Patient();
        $this->doctorModel = new Doctor();
    }

    public function index(): void
    {
        $date = $_GET['date'] ?? null;
        $doctor_id = isset($_GET['doctor_id']) && $_GET['doctor_id'] !== '' ? (int)$_GET['doctor_id'] : null;
        $patient_id = isset($_GET['patient_id']) && $_GET['patient_id'] !== '' ? (int)$_GET['patient_id'] : null;
        $search = $_GET['search'] ?? null;

        $slips = $this->slipModel->getAllFiltered($date, $doctor_id, $patient_id, $search);
        $patients = $this->patientModel->getAll();
        $doctors = $this->doctorModel->getAll();

        include __DIR__ . '/../views/DashboardView.php';
    }

    public function exportCsv(): void
    {
        $date = $_GET['date'] ?? null;
        $doctor_id = isset($_GET['doctor_id']) && $_GET['doctor_id'] !== '' ? (int)$_GET['doctor_id'] : null;
        $patient_id = isset($_GET['patient_id']) && $_GET['patient_id'] !== '' ? (int)$_GET['patient_id'] : null;
        $search = $_GET['search'] ?? null;

        $slips = $this->slipModel->getAllFiltered($date, $doctor_id, $patient_id, $search);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="bulletins_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, [
            'N° Bulletin',
            'Patient',
            'Médecin',
            'Date Soins',
            'Date Remboursement',
            'Total (TND)',
            'Déboursé (TND)',
            'Remboursé (TND)',
            'Différence (TND)'
        ], ';');

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
        }
        fclose($output);
        exit;
    }

    public function exportPdf(): void
    {
        $date = $_GET['date'] ?? null;
        $doctor_id = isset($_GET['doctor_id']) && $_GET['doctor_id'] !== '' ? (int)$_GET['doctor_id'] : null;
        $patient_id = isset($_GET['patient_id']) && $_GET['patient_id'] !== '' ? (int)$_GET['patient_id'] : null;
        $search = $_GET['search'] ?? null;

        $slips = $this->slipModel->getAllFiltered($date, $doctor_id, $patient_id, $search);
        $patients = $this->patientModel->getAll();
        $doctors = $this->doctorModel->getAll();
        
        include __DIR__ . '/../views/DashboardView.php';
        exit;
    }
}