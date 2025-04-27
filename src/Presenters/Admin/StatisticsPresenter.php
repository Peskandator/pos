<?php

declare(strict_types=1);

namespace App\Presenters\Admin;
use App\Company\Enums\CompanyUserRoles;
use App\Components\Breadcrumb\BreadcrumbItem;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Order\Services\OrdersFilter;
use App\Presenters\BaseCompanyPresenter;
use App\Utils\FlashMessageType;
use Nette\Application\UI\Form;

final class StatisticsPresenter extends BaseCompanyPresenter
{
    public function __construct(
        private readonly OrdersFilter $ordersFilter,
    )
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

        $this->checkPermissionsForUser([CompanyUserRoles::ADMIN]);

        $this->getComponent('breadcrumb')->addItem(new BreadcrumbItem('Statistika', null));

        $fromDay = $this->parseNullableInt($fromDay);
        $fromMonth = $this->parseNullableInt($fromMonth);
        $fromYear = $this->parseNullableInt($fromYear);
        $toDay = $this->parseNullableInt($toDay);
        $toMonth = $this->parseNullableInt($toMonth);
        $toYear = $this->parseNullableInt($toYear);

        $this->template->products = [];
        $this->template->totalPriceForYear = 0;

        try {
            [$start, $end, $isFiltered, $scale] = $this->getDateRange(
                $fromDay, $fromMonth, $fromYear,
                $toDay, $toMonth, $toYear,
                $fromDate, $toDate
            );

            if ($isFiltered && ($start > $end)) {
                $this->flashMessage('Počáteční datum nemůže být větší než koncové.', FlashMessageType::ERROR);
                $this->redirect('this');
                return;
            }

        } catch (\Exception $e) {
            $this->flashMessage('Neplatný formát datumu.', FlashMessageType::ERROR);
            $this->redirect('this');
            return;
        }

        $orders = $this->ordersFilter->getOrdersInRange($this->currentCompany, $start, $end);

        $this->template->filteredOrders = $orders;
        $this->template->filteredOrdersIds = json_encode($this->getFilteredOrdersIds($orders));

        $this->template->products = $this->getAggregatedProductData($orders);
        $this->template->totalPriceForYear = $this->getTotalPriceForYear($orders);
        $this->template->start = $start;
        $this->template->end = $end;
        $this->template->exportFileName = 'Objednávky od ' . $start->format('d-m-Y') . ' do ' . $end->format('d-m-Y');

        $dataForChart = $this->getSalesChartData($orders, $start, $end, $scale);
        $this->template->salesLabels = json_encode($dataForChart['labels']);
        $this->template->salesValues = json_encode($dataForChart['values']);
        $this->template->salesScale = $scale;
        $this->template->currentCompanyId = $this->currentCompanyId;

        $distribution = $this->getProductDistribution($orders);
        $this->template->productLabels = json_encode(array_keys($distribution), JSON_UNESCAPED_UNICODE);
        $this->template->productQuantities = json_encode(array_values($distribution));

        $revenueDistribution = $this->getProductRevenueDistribution($orders);
        $this->template->productRevenueLabels = json_encode(array_keys($revenueDistribution), JSON_UNESCAPED_UNICODE);
        $this->template->productRevenueValues = json_encode(array_values($revenueDistribution));
    }

    private function getSalesChartData(array $orders, \DateTimeImmutable $start, \DateTimeImmutable $end, string $scale): array
    {
        $intervalSpec = match ($scale) {
            'hour' => 'PT1H',
            'month' => 'P1M',
            default => 'P1D',
        };

        $format = match ($scale) {
            'hour' => 'H:00',
            'month' => 'Y-m',
            default => 'Y-m-d',
        };

        $totals = [];

        $period = $this->getInclusiveDatePeriod($start, $end, new \DateInterval($intervalSpec));
        foreach ($period as $point) {
            $label = $point->format($format);
            $totals[$label] = 0.0;
        }

        /** @var Order $order */
        foreach ($orders as $order) {
            $label = match ($scale) {
                'hour' => $order->getCreationDate()
                    ->setTime((int) $order->getCreationDate()->format('H'), 0)
                    ->format('H:00'),

                'month' => $order->getCreationDate()->format('Y-m'),
                default => $order->getCreationDate()->format('Y-m-d'),
            };


            if (!isset($totals[$label])) {
                continue;
            }

            /** @var OrderItem $orderItem */
            foreach ($order->getOrderItems() as $item) {
                $totals[$label] += $item->getQuantity() * $item->getPrice();
            }
        }

        ksort($totals);

        return [
            'labels' => array_keys($totals),
            'values' => array_values($totals),
        ];
    }

    private function getInclusiveDatePeriod(\DateTimeImmutable $start, \DateTimeImmutable $end, \DateInterval $interval): \Generator
    {
        $current = $start;
        while ($current <= $end) {
            yield $current;
            $current = $current->add($interval);
        }
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

        $orders = $this->currentCompany->getOrders()->toArray();
        $years = [];

        foreach ($orders as $order) {
            $year = (int) $order->getCreationDate()->format('Y');
            $years[$year] = $year;
        }

        ksort($years);
        $yearOptions = $years;

        $form->addSelect('fromDay', 'Den', array_combine(range(1, 31), range(1, 31)))->setPrompt('Den');
        $form->addSelect('fromMonth', 'Měsíc', array_combine(range(1, 12), range(1, 12)))->setPrompt('Měsíc');
        $form->addSelect('fromYear', 'Rok', $yearOptions)->setPrompt('Rok');

        $form->addText('fromDate', 'Od')->setHtmlType('date');
        $form->addText('toDate', 'Od')->setHtmlType('date');

        $form->addSubmit('send', 'Zobrazit');

        $params = $this->getParameters();

        if (isset($params['fromYear']) && !in_array((int)$params['fromYear'], $years, true)) {
            unset($params['fromYear']);
        }
        if (isset($params['toYear']) && !in_array((int)$params['toYear'], $years, true)) {
            unset($params['toYear']);
        }

        if (
            isset($params['fromDay'], $params['fromMonth'], $params['fromYear']) &&
            is_numeric($params['fromDay']) && is_numeric($params['fromMonth']) && is_numeric($params['fromYear'])
        ) {
            $y = (int) $params['fromYear'];
            $m = (int) $params['fromMonth'];
            $d = (int) $params['fromDay'];

            if ($m >= 1 && $m <= 12) {
                $lastDay = (int)(new \DateTimeImmutable("$y-$m-01"))
                    ->modify('last day of this month')
                    ->format('j');

                if ($d > $lastDay) {
                    $params['fromDay'] = $lastDay;
                }
            }
        }

        $form->setDefaults($params);

        $form->onValidate[] = function (Form $form, \stdClass $values): void {
            $fromDate = $values->fromDate;
            $toDate = $values->toDate;

            $fromDateTime = null;
            $toDateTime = null;

            $today = new \DateTimeImmutable('today');

            if ($fromDate) {
                $fromDateTime = new \DateTimeImmutable($fromDate . ' 00:00:00');

//                if ($fromDateTime->getTimestamp() > $today->getTimestamp()) {
//                    $msg = 'Počáteční datum nemůže být v budoucnosti.';
//                    $this->flashMessage($msg, FlashMessageType::ERROR);
//                    $form['fromDate']->addError($msg);
//                }
            }

            if ($toDate) {
                $toDateTime = new \DateTimeImmutable($toDate . ' 23:59:59');
            }

            if ($fromDateTime && $toDateTime && $fromDateTime > $toDateTime) {
                $msg = 'Počáteční datum nemůže být větší než koncové.';
                $this->flashMessage($msg, FlashMessageType::ERROR);
                $form['fromDate']->addError($msg);
                return;
            }

            if (($fromDate || $toDate) && ($values->fromDay || $values->fromMonth || $values->fromYear)) {
                $msg = 'Nelze filtrovat podle datumu a období zároveň. Vyplňte prosím jen jeden filtr.';
                $this->flashMessage($msg, FlashMessageType::ERROR);
                $form->addError($msg);
            }
        };

        $form->onSuccess[] = function (Form $form, \stdClass $values) {
            $params = [];

            if ($values->fromDay) {
                $params['fromDay'] = (int)$values->fromDay;
            } else {
                $params['fromDay'] = null;
            }

            if ($values->fromMonth) {
                $params['fromMonth'] = (int)$values->fromMonth;
            } else {
                $params['fromMonth'] = null;
            }

            if ($values->fromYear) {
                $params['fromYear'] = (int)$values->fromYear;
            } else {
                $params['fromYear'] = null;
            }

            if ($values->fromDate) {
                $params['fromDate'] = $values->fromDate;
            } else {
                $params['fromDate'] = null;
            }

            if ($values->toDate) {
                $params['toDate'] = $values->toDate;
            } else {
                $params['toDate'] = null;
            }

            $this->flashMessage('Výsledky byly vyfiltrovány.', FlashMessageType::SUCCESS);
            $this->redirect('this', $params);
        };

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
        $today = $now->setTime(0, 0);

        $isFiltered = ($fromDate !== null && $fromDate !== '') ||
            ($toDate !== null && $toDate !== '') ||
            ($fromDay !== null) ||
            ($fromMonth !== null) ||
            ($fromYear !== null) ||
            ($toDay !== null) ||
            ($toMonth !== null) ||
            ($toYear !== null);

        if ($fromDate && $toDate) {
            $start = new \DateTimeImmutable($fromDate . ' 00:00:00');
            $end = new \DateTimeImmutable($toDate . ' 23:59:59');
            return [$start, $end, true, $this->determineScale($start, $end)];
        }

        if ($fromDay && $fromMonth && $fromYear) {
            $start = new \DateTimeImmutable("$fromYear-$fromMonth-$fromDay 00:00:00");
            $end = new \DateTimeImmutable("$fromYear-$fromMonth-$fromDay 23:59:59");
            [$start, $end] = $this->adjustFutureRange($start, $end, $today);
            return [$start, $end, true, $this->determineScale($start, $end)];
        }

        if ($fromDay && !$fromMonth && !$fromYear) {
            $target = $today->setDate((int)$today->format('Y'), (int)$today->format('n'), $fromDay);
            if ($target > $today) {
                $target = $target->modify('-1 year');
            }
            $start = $target->setTime(0, 0);
            $end = $target->setTime(23, 59, 59);
            return [$start, $end, true, $this->determineScale($start, $end)];
        }

        if ($fromMonth && !$fromDay && !$fromYear) {
            $year = (int)$now->format('Y');
            $start = new \DateTimeImmutable("$year-$fromMonth-01 00:00:00");
            $end = $start->modify('last day of this month')->setTime(23, 59, 59);
            [$start, $end] = $this->adjustFutureRange($start, $end, $today);
            return [$start, $end, true, $this->determineScale($start, $end)];
        }

        if ($fromYear && !$fromDay && !$fromMonth) {
            $start = new \DateTimeImmutable("$fromYear-01-01 00:00:00");
            $end = new \DateTimeImmutable("$fromYear-12-31 23:59:59");
            [$start, $end] = $this->adjustFutureRange($start, $end, $today);
            return [$start, $end, true, $this->determineScale($start, $end)];
        }

        if ($fromDay && $fromMonth && !$fromYear) {
            $year = (int)$now->format('Y');
            $start = new \DateTimeImmutable("$year-$fromMonth-$fromDay 00:00:00");
            $end = new \DateTimeImmutable("$year-$fromMonth-$fromDay 23:59:59");
            [$start, $end] = $this->adjustFutureRange($start, $end, $today);
            return [$start, $end, true, $this->determineScale($start, $end)];
        }

        if ($fromDay && !$fromMonth && $fromYear) {
            $month = (int)$now->format('n');
            $start = new \DateTimeImmutable("$fromYear-$month-$fromDay 00:00:00");
            $end = new \DateTimeImmutable("$fromYear-$month-$fromDay 23:59:59");
            [$start, $end] = $this->adjustFutureRange($start, $end, $today);
            return [$start, $end, true, $this->determineScale($start, $end)];
        }

        if ($fromMonth && $fromYear && !$fromDay) {
            $start = new \DateTimeImmutable("$fromYear-$fromMonth-01 00:00:00");
            $end = $start->modify('last day of this month')->setTime(23, 59, 59);
            [$start, $end] = $this->adjustFutureRange($start, $end, $today);
            return [$start, $end, true, $this->determineScale($start, $end)];
        }

        if ($isFiltered) {
            $fromYear = $fromYear ?? (int) $now->format('Y');
            $toYear = $toYear ?? $fromYear;
            $fromMonth = $fromMonth ?? 1;
            $toMonth = $toMonth ?? 12;
            $fromDay = $fromDay ?? 1;

            $lastDayOfFromMonth = (new \DateTimeImmutable("$fromYear-$fromMonth-01"))
                ->modify('last day of this month')->format('d');
            $fromDay = min($fromDay, (int)$lastDayOfFromMonth);

            $lastDayOfToMonth = (new \DateTimeImmutable("$toYear-$toMonth-01"))
                ->modify('last day of this month')->format('d');
            $toDay = $toDay ?? (int)$lastDayOfToMonth;
            $toDay = min($toDay, (int)$lastDayOfToMonth);

            $start = new \DateTimeImmutable("$fromYear-$fromMonth-$fromDay 00:00:00");
            $end = new \DateTimeImmutable("$toYear-$toMonth-$toDay 23:59:59");
            [$start, $end] = $this->adjustFutureRange($start, $end, $today);
            return [$start, $end, true, $this->determineScale($start, $end)];
        }

        $start = $now->modify('monday this week')->setTime(0, 0);
        $end = $now->modify('sunday this week')->setTime(23, 59, 59);
        return [$start, $end, false, $this->determineScale($start, $end)];
    }

    private function adjustFutureRange(\DateTimeImmutable $start, \DateTimeImmutable $end, \DateTimeImmutable $today): array
    {
        if ($start > $today) {
            $start = $start->modify('-1 year');
            $end = $end->modify('-1 year');
        }

        return [$start, $end];
    }

    private function determineScale(\DateTimeImmutable $start, \DateTimeImmutable $end): string
    {
        $days = $end->diff($start)->days;

        return match (true) {
            $days === 0 => 'hour',
            $days <= 31 => 'day',
            default => 'month',
        };
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

    private function getFilteredOrdersIds(array $orders): array
    {
        $ids = [];
        /** @var Order $order */
        foreach ($orders as $order) {
            $ids[] = $order->getId();
        }

        return $ids;
    }
}