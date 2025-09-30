<?php

namespace App\Controllers;

class Accountant extends BaseController
{
    public function index()
    {
        return $this->dashboard();
    }

    public function dashboard()
    {
        return view('accountant/dashboard');
    }

    public function billing()
    {
        return view('accountant/billing');
    }

    public function payments()
    {
        return view('accountant/payments');
    }

    public function insurance()
    {
        return view('accountant/insurance');
    }

    public function createInvoice()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to(base_url('accountant/billing'));
        }

        $payload = $this->request->getPost([
            'patient_name', 'service', 'amount', 'due_date'
        ]);

        $errors = [];
        if (empty($payload['patient_name'])) $errors['patient_name'] = 'Patient name is required';
        if (empty($payload['service'])) $errors['service'] = 'Service/description is required';
        if (!isset($payload['amount']) || !is_numeric($payload['amount']) || (float)$payload['amount'] <= 0) $errors['amount'] = 'Amount must be greater than 0';
        if (empty($payload['due_date'])) $errors['due_date'] = 'Due date is required';

        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        // Stub: pretend we created an invoice in DB and return an invoice number
        $invoiceNo = 'INV-' . date('Ymd-His');

        return redirect()->to(base_url('accountant/billing'))
            ->with('success', 'Invoice ' . $invoiceNo . ' created successfully.');
    }

    public function processPayment()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to(base_url('accountant/payments'));
        }

        $payload = $this->request->getPost([
            'invoice_no', 'amount', 'method', 'reference'
        ]);

        $errors = [];
        if (empty($payload['invoice_no'])) $errors['invoice_no'] = 'Invoice number is required';
        if (!isset($payload['amount']) || !is_numeric($payload['amount']) || (float)$payload['amount'] <= 0) $errors['amount'] = 'Amount must be greater than 0';
        $allowedMethods = ['Cash','Credit Card','Check','Insurance'];
        if (empty($payload['method']) || !in_array($payload['method'], $allowedMethods, true)) $errors['method'] = 'Invalid payment method';

        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        // Stub: pretend payment processed
        return redirect()->to(base_url('accountant/payments'))
            ->with('success', 'Payment processed for ' . htmlspecialchars((string)$payload['invoice_no'], ENT_QUOTES, 'UTF-8'));
    }

    public function submitClaim()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to(base_url('accountant/insurance'));
        }

        $payload = $this->request->getPost([
            'patient_name', 'policy_no', 'claim_amount', 'diagnosis_code', 'notes'
        ]);

        $errors = [];
        if (empty($payload['patient_name'])) $errors['patient_name'] = 'Patient name is required';
        if (empty($payload['policy_no'])) $errors['policy_no'] = 'Policy number is required';
        if (!isset($payload['claim_amount']) || !is_numeric($payload['claim_amount']) || (float)$payload['claim_amount'] <= 0) $errors['claim_amount'] = 'Claim amount must be greater than 0';
        if (empty($payload['diagnosis_code'])) $errors['diagnosis_code'] = 'Diagnosis code is required';

        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        // Stub: pretend claim submitted
        $ref = 'CLM-' . date('Ymd-His');
        return redirect()->to(base_url('accountant/insurance'))
            ->with('success', 'Insurance claim ' . $ref . ' submitted successfully.');
    }
}
