<?php

namespace App\Controller;

use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(TransactionRepository $transactionRepository): Response
    {
        $user = $this->getUser();
        $transactions = $transactionRepository->findBy(['user' => $user]);

        $totalIncome = 0;
        $totalExpense = 0;
        $categoryStats = [];

        foreach ($transactions as $transaction) {
            $amount = $transaction->getAmount();
            $type = $transaction->getCategory()->getType();
            $category = $transaction->getCategory()->getName();

            if ($type === 'income') {
                $totalIncome += $amount;
            } elseif ($type === 'expense') {
                $totalExpense += $amount;
                $categoryStats[$category] = ($categoryStats[$category] ?? 0) + $amount;
            }
        }

        $balance = $totalIncome - $totalExpense;

        return $this->render('dashboard/index.html.twig', [
            'balance' => $balance,
            'income' => $totalIncome,
            'expense' => $totalExpense,
            'categoryLabels' => array_keys($categoryStats),
            'categoryValues' => array_values($categoryStats),
        ]);

    }
}
