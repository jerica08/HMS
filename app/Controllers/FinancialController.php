<?php

namespace App\Controllers;

use App\Models\FinancialTransactionModel;
use App\Models\CategoryModel;

class FinancialManagement extends BaseController
{
    protected $transactionModel;
    protected $categoryModel;

    public function __construct()
    {
        $this->transactionModel = new FinancialTransactionModel();
        $this->categoryModel = new CategoryModel();
    }

    public function demo()
    {
        return view('unified/financial-modal-demo');
    }

    public function test()
    {
        return view('unified/financial-test');
    }

    public function index()
    {
        try {
            $data = [
                'title' => 'Financial Management',
                'transactions' => $this->transactionModel->getTransactionsWithDetails(),
                'summary' => $this->transactionModel->getFinancialSummary(),
                'categories' => $this->categoryModel->getCategoriesGrouped(),
            ];

            return view('unified/financial-management', $data);
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
            echo "<br><br>Financial management system is being set up. Please try again in a moment.";
        }
    }

    public function addTransaction()
    {
        if ($this->request->getMethod() === 'POST') {
            $validationRules = [
                'user_id' => 'required|integer|greater_than[0]',
                'type' => 'required|in_list[Income,Expense]',
                'category_id' => 'required|integer|greater_than[0]',
                'amount' => 'required|numeric|greater_than[0]',
                'transaction_date' => 'required|valid_date[Y-m-d]',
            ];

            if ($this->validate($validationRules)) {
                $data = [
                    'user_id' => $this->request->getPost('user_id'),
                    'type' => $this->request->getPost('type'),
                    'category_id' => $this->request->getPost('category_id'),
                    'amount' => $this->request->getPost('amount'),
                    'description' => $this->request->getPost('description'),
                    'transaction_date' => $this->request->getPost('transaction_date'),
                ];

                if ($this->transactionModel->insert($data)) {
                    // Check if this is an AJAX request
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON([
                            'status' => 'success',
                            'message' => 'Transaction added successfully!'
                        ]);
                    } else {
                        return redirect()->to('/financial-management')->with('success', 'Transaction added successfully!');
                    }
                } else {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Failed to add transaction.'
                        ]);
                    } else {
                        return redirect()->back()->with('error', 'Failed to add transaction.');
                    }
                }
            } else {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Please correct the errors in the form.',
                        'errors' => $this->validator->getErrors()
                    ]);
                } else {
                    return redirect()->back()->with('error', 'Please correct the errors in the form.')->with('validation', $this->validator);
                }
            }
        }

        $data = [
            'title' => 'Add Transaction',
            'categories' => $this->categoryModel->getCategoriesGrouped(),
            'users' => $this->getUsers(),
        ];

        return view('unified/add-transaction', $data);
    }

    public function getUsersAPI()
    {
        $users = $this->getUsers();
        return $this->response->setJSON(['users' => $users]);
    }

    public function getCategoriesByType()
    {
        $type = $this->request->getGet('type');
        
        if ($type === 'all') {
            // Return all categories grouped by type
            $categories = $this->categoryModel->getCategoriesGrouped();
            return $this->response->setJSON($categories);
        } else {
            // Return categories for specific type
            $categories = $this->categoryModel->getCategoriesByType($type);
            return $this->response->setJSON($categories);
        }
    }

    private function getUsers()
    {
        $db = \Config\Database::connect();
        return $db->table('users')->select('id, username')->get()->getResultArray();
    }
}
