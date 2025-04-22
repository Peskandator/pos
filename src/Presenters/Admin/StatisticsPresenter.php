<?php

declare(strict_types=1);

namespace App\Presenters\Admin;
use App\Components\Breadcrumb\BreadcrumbItem;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Presenters\BaseCompanyPresenter;
use App\Utils\FlashMessageType;
use Nette\Application\UI\Form;

final class StatisticsPresenter extends BaseCompanyPresenter
{
    public function __construct()
    {
        parent::__construct();
    }

    public function actionDefault(
        $fromDay = null,
        $fromMonth = null,
        $fromYear = null,
        $toDay = null,
        $toMonth = null,
        $toYear = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ): void {
        $this->getComponent('breadcrumb')->addItem(new BreadcrumbItem('Statistika', null));

        $now = new \DateTimeImmutable();

        if ($fromDay && !$fromMonth) $fromMonth = (string) $now->format('n');
        if ($fromDay && !$fromYear) $fromYear = (string) $now->format('Y');
        if ($toDay && !$toMonth) $toMonth = (string) $now->format('n');
        if ($toDay && !$toYear) $toYear = (string) $now->format('Y');

        $fromDay = $this->parseNullableInt($fromDay);
        $fromMonth = $this->parseNullableInt($fromMonth);
        $fromYear = $this->parseNullableInt($fromYear);
        $toDay = $this->parseNullableInt($toDay);
        $toMonth = $this->parseNullableInt($toMonth);
        $toYear = $this->parseNullableInt($toYear);

        $this->template->products = [];
        $this->template->totalPriceForYear = 0;

        try {
            [$start, $end, $isFiltered] = $this->getDateRange(
                $fromDay, $fromMonth, $fromYear,
                $toDay, $toMonth, $toYear,
                $fromDate, $toDate
            );

            if ($isFiltered && ($start > $end)) {
                $this->flashMessage('Počáteční datum nemůže být větší než koncové.', FlashMessageType::ERROR);
                return;
            }

        } catch (\Exception $e) {
            $this->flashMessage('Neplatný formát datumu.', FlashMessageType::ERROR);
            return;
        }

        $orders = $this->getOrdersInRange($start, $end);
        $this->template->filteredOrders = $orders;

        // bdump($start->format('Y-m-d H:i:s'), 'Start Date');
        // bdump($end->format('Y-m-d H:i:s'), 'End Date');
        // bdump(count($orders), 'Orders Found');

        $this->template->products = $this->getAggregatedProductData($orders);
        $this->template->totalPriceForYear = $this->getTotalPriceForYear($orders);
        $this->template->start = $start;
        $this->template->end = $end;

        $dataForChart = $this->getSalesChartData($orders, $start, $end);
        $this->template->salesLabels = json_encode($dataForChart['labels']);
        $this->template->salesValues = json_encode($dataForChart['values']);
        $this->template->currentCompanyId = $this->currentCompanyId;

        $distribution = $this->getProductDistribution($orders);
        // bdump($distribution, 'Distribution for donut chart');
        // bdump(array_keys($distribution), 'Product labels');
        // bdump(array_values($distribution), 'Product values');

        $this->template->productLabels = json_encode(array_keys($distribution), JSON_UNESCAPED_UNICODE);
        $this->template->productQuantities = json_encode(array_values($distribution));

        $revenueDistribution = $this->getProductRevenueDistribution($orders);
        $this->template->productRevenueLabels = json_encode(array_keys($revenueDistribution), JSON_UNESCAPED_UNICODE);
        $this->template->productRevenueValues = json_encode(array_values($revenueDistribution));
    }

    private function getSalesChartData(array $orders, \DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        $dailyTotals = [];
        $period = new \DatePeriod($start, new \DateInterval('P1D'), $end->modify('+1 day'));
        foreach ($period as $date) {
            $dailyTotals[$date->format('Y-m-d')] = 0.0;
        }

        /** @var Order $order */
        foreach ($orders as $order) {
            $dateKey = $order->getCreationDate()->format('Y-m-d');

            if (!isset($dailyTotals[$dateKey])) {
                continue;
            }

            /** @var OrderItem $orderItem */
            foreach ($order->getOrderItems() as $item) {
                $quantity = $item->getQuantity();
                $price = $item->getPrice();

                $total = $quantity * $price;

                $dailyTotals[$dateKey] += $total;
            }
        }

        ksort($dailyTotals);

        // bdump($dailyTotals, 'Daily Totals Raw (Date => Kč)');

        return [
            'labels' => array_keys($dailyTotals),
            'values' => array_values($dailyTotals),
        ];
    }

    private function getProductDistribution(array $orders): array
    {
        $distribution = [];

        /** @var Order $order */
        foreach ($orders as $order) {
            /** @var OrderItem $orderItem */
            foreach ($order->getOrderItems() as $item) {
                $name = $item->getProductName();
                $distribution[$name] = ($distribution[$name] ?? 0) + $item->getQuantity();
            }
        }

        arsort($distribution);
        return $distribution;
    }

    private function getProductRevenueDistribution(array $orders): array
    {
        $revenue = [];

        /** @var Order $order */
        foreach ($orders as $order) {
            /** @var OrderItem $orderItem */
            foreach ($order->getOrderItems() as $item) {
                $name = $item->getProductName();
                $revenue[$name] = ($revenue[$name] ?? 0) + ($item->getQuantity() * $item->getPrice());
            }
        }

        arsort($revenue);
        return $revenue;
    }

    protected function createComponentFilterForm(): Form
    {
        $form = new Form;
        $form->setMethod('GET');

        $orders = $this->currentCompany->getOrders()->toArray();
        $years = [];

        foreach ($orders as $order) {
            $year = (int) $order->getCreationDate()->format('Y');
            $years[$year] = $year;
        }

        ksort($years);
        // bdump($years, 'Years extracted from orders'); // 👈 Add this
        $yearOptions = $years;

        $form->addSelect('fromDay', 'Den od', array_combine(range(1, 31), range(1, 31)))->setPrompt('Den');
        $form->addSelect('fromMonth', 'Měsíc od', array_combine(range(1, 12), range(1, 12)))->setPrompt('Měsíc');
        $form->addSelect('fromYear', 'Rok od', $yearOptions)->setPrompt('Rok');

        $form->addText('fromDate', 'Od')->setHtmlType('date');

        $form->addSubmit('send', 'Zobrazit');

        $params = $this->getParameters();

        if (isset($params['fromYear']) && !in_array((int)$params['fromYear'], $years, true)) {
            unset($params['fromYear']);
        }
        if (isset($params['toYear']) && !in_array((int)$params['toYear'], $years, true)) {
            unset($params['toYear']);
        }

        foreach (['from', 'to'] as $prefix) {
            $dayKey = "{$prefix}Day";
            $monthKey = "{$prefix}Month";
            $yearKey = "{$prefix}Year";

            if (
                isset($params[$dayKey], $params[$monthKey], $params[$yearKey]) &&
                is_numeric($params[$dayKey]) && is_numeric($params[$monthKey]) && is_numeric($params[$yearKey])
            ) {
                $y = (int) $params[$yearKey];
                $m = (int) $params[$monthKey];
                $d = (int) $params[$dayKey];

                if ($m >= 1 && $m <= 12) {
                    $lastDay = cal_days_in_month(CAL_GREGORIAN, $m, $y);
                    // bdump($lastDay, "Last day of $m/$y");

                    if ($d > $lastDay) {
                        $params[$dayKey] = $lastDay;
                    }
                }
            }
        }

        $form->setDefaults($params);

        $form->onValidate[] = function (Form $form): void {
            $values = $form->getValues('array');
            foreach (['from', 'to'] as $prefix) {
                $dayKey = "{$prefix}Day";
                $monthKey = "{$prefix}Month";
                $yearKey = "{$prefix}Year";

                $y = (int) ($values[$yearKey] ?? 0);
                $m = (int) ($values[$monthKey] ?? 0);
                $d = (int) ($values[$dayKey] ?? 0);

                if ($y && $m && $d && !checkdate($m, $d, $y)) {
                    $lastDay = cal_days_in_month(CAL_GREGORIAN, $m, $y);
                    if ($form[$dayKey] instanceof \Nette\Forms\Controls\SelectBox) {
                        $form[$dayKey]->setValue($lastDay);
                    }
                }
            }
        };

        $form->onSubmit[] = function () {};

        return $form;
    }

    private function parseNullableInt(?string $value): ?int
    {
        return ($value === null || $value === '') ? null : (int) $value;
    }

    private function getDateRange(
        ?int $fromDay, ?int $fromMonth, ?int $fromYear,
        ?int $toDay, ?int $toMonth, ?int $toYear,
        ?string $fromDate, ?string $toDate
    ): array {
        $now = new \DateTimeImmutable();
        $isFiltered = ($fromDate !== null && $fromDate !== '') ||
            ($toDate !== null && $toDate !== '') ||
            ($fromDay !== null && $fromDay !== '') ||
            ($fromMonth !== null && $fromMonth !== '') ||
            ($fromYear !== null && $fromYear !== '') ||
            ($toDay !== null && $toDay !== '') ||
            ($toMonth !== null && $toMonth !== '') ||
            ($toYear !== null && $toYear !== '');


        if ($fromDate && $toDate) {
            return [
                new \DateTimeImmutable($fromDate . ' 00:00:00'),
                new \DateTimeImmutable($toDate . ' 23:59:59'),
                true
            ];
        }

        if ($isFiltered) {
            $fromYear = $fromYear ?? (int) $now->format('Y');
            $toYear = $toYear ?? $fromYear;
            $fromMonth = $fromMonth ?? 1;
            $toMonth = $toMonth ?? 12;
            $fromDay = $fromDay ?? 1;

            $lastDayOfFromMonth = (new \DateTimeImmutable("$fromYear-$fromMonth-01"))
                ->modify('last day of this month')
                ->format('d');
            $fromDay = min($fromDay, (int)$lastDayOfFromMonth);

            $lastDayOfToMonth = (new \DateTimeImmutable("$toYear-$toMonth-01"))
                ->modify('last day of this month')
                ->format('d');
            $toDay = $toDay ?? (int) $lastDayOfToMonth;
            $toDay = min($toDay, (int) $lastDayOfToMonth);

            return [
                new \DateTimeImmutable("$fromYear-$fromMonth-$fromDay 00:00:00"),
                new \DateTimeImmutable("$toYear-$toMonth-$toDay 23:59:59"),
                true
            ];
        }


        $monday = $now->modify('monday last week')->setTime(0, 0);
        $sunday = $now->modify('sunday this week')->setTime(23, 59, 59);

        return [$monday, $sunday, false];
    }

    private function getOrdersInRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $filteredOrders = [];

        /** @var Order $order */
        foreach ($this->currentCompany->getOrders() as $order) {
            $createdAt = $order->getCreationDate();

            if ($createdAt >= $start && $createdAt <= $end) {
                $filteredOrders[] = $order;
            }
        }

        return $filteredOrders;
    }

    private function getTotalPriceForYear(array $orders): float
    {
        $totalPrice = 0;
        /** @var Order $order */
        foreach ($orders as $order) {
            $totalPrice += $order->getTotalPrice();
        }

        return $totalPrice;
    }

    private function getAggregatedProductData(array $orders): array
    {
        $products = [];

        /** @var Order $order */
        foreach ($orders as $order) {
            /** @var OrderItem $orderItem */
            foreach ($order->getOrderItems() as $orderItem) {
                $product = $orderItem->getProduct();
                $productId = $product->getId();

                if (!isset($products[$productId])) {
                    $products[$productId] = [
                        'product' => $product,
                        'quantity' => 0,
                        'totalPrice' => 0.0,
                    ];
                }

                $products[$productId]['quantity'] += $orderItem->getQuantity();
                $products[$productId]['totalPrice'] += $orderItem->getTotalPrice();
            }
        }

        foreach ($products as &$data) {
            $data['unitPrice'] = $data['quantity'] > 0 ? $data['totalPrice'] / $data['quantity'] : 0;
        }

        return $products;
    }
}