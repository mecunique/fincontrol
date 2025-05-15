<?php

namespace App\Controller;

use App\Repository\TransactionRepository;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(Request $request, TransactionRepository $transactionRepository): Response
    {
        $user = $this->getUser();
        $currentDate = new \DateTime();

        $month = $request->query->getInt('month', (int) $currentDate->format('m'));
        $year = $request->query->getInt('year', (int) $currentDate->format('Y'));

        $startDate = new \DateTime("$year-$month-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month')->setTime(23, 59, 59);

        $transactions = $transactionRepository->createQueryBuilder('t')
            ->join('t.category', 'c')
            ->where('t.user = :user')
            ->andWhere('t.date BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('t.date', 'DESC')
            ->getQuery()
            ->getResult();

        $income = 0;
        $expense = 0;
        $categoryStats = [];

        foreach ($transactions as $transaction) {
            $amount = $transaction->getAmount();
            $type = $transaction->getCategory()->getType();
            $category = $transaction->getCategory()->getName();

            if ($type === 'income') {
                $income += $amount;
            } elseif ($type === 'expense') {
                $expense += $amount;
                $categoryStats[$category] = ($categoryStats[$category] ?? 0) + $amount;
            }
        }

        $balance = $income - $expense;

        return $this->render('dashboard/index.html.twig', [
            'transactions' => $transactions,
            'income' => $income,
            'expense' => $expense,
            'balance' => $balance,
            'categoryLabels' => array_keys($categoryStats),
            'categoryValues' => array_values($categoryStats),
            'month' => $month,
            'year' => $year,
            'now' => $currentDate,
        ]);
    }

    #[Route('/dashboard/pdf', name: 'app_dashboard_pdf')]
    public function exportPdf(TransactionRepository $transactionRepository, Request $request): Response
    {
        $user = $this->getUser();
        $month = $request->query->getInt('month', (int) date('m'));
        $year = $request->query->getInt('year', (int) date('Y'));

        $transactions = $transactionRepository->findByUserAndMonthYear($user, $month, $year);

        $income = 0;
        $expense = 0;

        foreach ($transactions as $transaction) {
            $amount = $transaction->getAmount();
            $type = $transaction->getCategory()->getType();

            if ($type === 'income') {
                $income += $amount;
            } elseif ($type === 'expense') {
                $expense += $amount;
            }
        }

        $balance = $income - $expense;

        $html = $this->renderView('dashboard/pdf.html.twig', [
            'transactions' => $transactions,
            'income' => $income,
            'expense' => $expense,
            'balance' => $balance,
            'month' => $month,
            'year' => $year,
        ]);

        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->render();

        return new Response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="dashboard.pdf"'
        ]);
    }
}
