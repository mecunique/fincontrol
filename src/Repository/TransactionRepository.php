<?php

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * Cette méthode permet de récupérer toutes les transactions d'un utilisateur
     * pour un mois et une année spécifiques.
     *
     * @param User $user L'utilisateur connecté
     * @param int $month Le mois (1 à 12)
     * @param int $year L'année (ex: 2025)
     * @return Transaction[] Tableau des transactions trouvées
     */
    public function findByUserAndMonthYear(User $user, int $month, int $year): array
    {
        // Crée une date de début au premier jour du mois
        $startDate = new \DateTimeImmutable("$year-$month-01");

        // Crée une date de fin au dernier jour du mois à 23:59:59
        $endDate = $startDate->modify('last day of this month')->setTime(23, 59, 59);

        // Requête pour récupérer les transactions de l'utilisateur dans cette période
        return $this->createQueryBuilder('t')
            ->join('t.category', 'c') // Jointure pour permettre le tri ou le filtrage par catégorie si besoin
            ->where('t.user = :user')
            ->andWhere('t.date BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('t.date', 'DESC') // Trie les résultats de la plus récente à la plus ancienne
            ->getQuery()
            ->getResult();
    }
}
