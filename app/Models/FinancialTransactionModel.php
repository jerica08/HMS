<?php

namespace App\Models;

use CodeIgniter\Model;

class FinancialTransactionModel extends Model
{
    protected $table            = 'financial_transactions';
    protected $primaryKey       = 'transaction_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'type',
        'category_id',
        'amount',
        'description',
        'transaction_date',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    // Validation
    protected $validationRules      = [
        'user_id'           => 'required|integer|greater_than[0]',
        'type'              => 'required|in_list[Income,Expense]',
        'category_id'       => 'required|integer|greater_than[0]',
        'amount'            => 'required|numeric|greater_than[0]',
        'transaction_date'  => 'required|valid_date[Y-m-d]',
    ];
    protected $validationMessages   = [
        'user_id' => [
            'required' => 'User is required',
            'integer' => 'User ID must be a valid integer',
            'greater_than' => 'Please select a valid user',
        ],
        'type' => [
            'required' => 'Transaction type is required',
            'in_list' => 'Invalid transaction type',
        ],
        'category_id' => [
            'required' => 'Category is required',
            'integer' => 'Category ID must be a valid integer',
            'greater_than' => 'Please select a valid category',
        ],
        'amount' => [
            'required' => 'Amount is required',
            'numeric' => 'Amount must be a valid number',
            'greater_than' => 'Amount must be greater than 0',
        ],
        'transaction_date' => [
            'required' => 'Transaction date is required',
            'valid_date' => 'Please enter a valid date',
        ],
    ];
    protected $skipValidation       = false;

    /**
     * Get transactions with user and category information
     */
    public function getTransactionsWithDetails($filters = [])
    {
        $builder = $this->select('financial_transactions.*, 
                                 users.username as user_name,
                                 categories.name as category_name')
                         ->join('users', 'users.id = financial_transactions.user_id')
                         ->join('categories', 'categories.category_id = financial_transactions.category_id');

        // Apply filters
        if (!empty($filters['type'])) {
            $builder->where('financial_transactions.type', $filters['type']);
        }
        
        if (!empty($filters['category_id'])) {
            $builder->where('financial_transactions.category_id', $filters['category_id']);
        }
        
        if (!empty($filters['user_id'])) {
            $builder->where('financial_transactions.user_id', $filters['user_id']);
        }
        
        if (!empty($filters['date_from'])) {
            $builder->where('financial_transactions.transaction_date >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $builder->where('financial_transactions.transaction_date <=', $filters['date_to']);
        }
        
        if (!empty($filters['search'])) {
            $builder->groupStart()
                    ->like('financial_transactions.description', $filters['search'])
                    ->orLike('categories.name', $filters['search'])
                    ->orLike('users.username', $filters['search'])
                    ->groupEnd();
        }

        return $builder->orderBy('financial_transactions.transaction_date DESC, financial_transactions.created_at DESC')
                       ->findAll();
    }

    /**
     * Get financial summary
     */
    public function getFinancialSummary($dateFrom = null, $dateTo = null)
    {
        $builder = $this->builder();
        
        if ($dateFrom) {
            $builder->where('transaction_date >=', $dateFrom);
        }
        
        if ($dateTo) {
            $builder->where('transaction_date <=', $dateTo);
        }

        $totalIncome = $builder->where('type', 'Income')
                               ->selectSum('amount')
                               ->get()
                               ->getRow()
                               ->amount ?? 0;

        $totalExpenses = $builder->where('type', 'Expense')
                                 ->selectSum('amount')
                                 ->get()
                                 ->getRow()
                                 ->amount ?? 0;

        return [
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_total' => $totalIncome - $totalExpenses,
        ];
    }

    /**
     * Get transactions by category for reporting
     */
    public function getTransactionsByCategory($dateFrom = null, $dateTo = null)
    {
        $builder = $this->select('categories.name, 
                                 categories.type,
                                 SUM(CASE WHEN financial_transactions.type = "Income" THEN amount ELSE 0 END) as income,
                                 SUM(CASE WHEN financial_transactions.type = "Expense" THEN amount ELSE 0 END) as expense,
                                 COUNT(financial_transactions.transaction_id) as transaction_count')
                         ->join('categories', 'categories.category_id = financial_transactions.category_id')
                         ->groupBy('categories.category_id');
        
        if ($dateFrom) {
            $builder->where('financial_transactions.transaction_date >=', $dateFrom);
        }
        
        if ($dateTo) {
            $builder->where('financial_transactions.transaction_date <=', $dateTo);
        }

        return $builder->orderBy('categories.name')
                       ->get()
                       ->getResultArray();
    }

    /**
     * Get monthly trend data
     */
    public function getMonthlyTrend($months = 12)
    {
        $builder = $this->select('DATE_FORMAT(transaction_date, "%Y-%m") as month,
                                 SUM(CASE WHEN type = "Income" THEN amount ELSE 0 END) as income,
                                 SUM(CASE WHEN type = "Expense" THEN amount ELSE 0 END) as expense')
                         ->where('transaction_date >=', date('Y-m-d', strtotime("-$months months")))
                         ->groupBy('DATE_FORMAT(transaction_date, "%Y-%m")')
                         ->orderBy('month');

        return $builder->get()->getResultArray();
    }
}
