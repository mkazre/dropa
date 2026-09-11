<?php

declare(strict_types=1);

namespace App\Controllers\Manage;

use App\Controllers\BaseController;
use App\Libraries\PaymentService;
use App\Models\PaymentModel;
use App\Models\ReservationModel;

class PaymentsController extends BaseController
{
    public function index()
    {
        $propertyId     = auth()->user()->property_id;
        $reservations   = model(ReservationModel::class)->where('property_id', $propertyId)->findAll();
        $reservationIds = array_column($reservations, 'id');
        $reservationsById = array_column($reservations, null, 'id');

        $payments = empty($reservationIds)
            ? []
            : model(PaymentModel::class)->whereIn('reservation_id', $reservationIds)
                ->where('method', 'manual')
                ->orderBy('id', 'DESC')
                ->findAll();

        return view('manage/payments/index', [
            'title'      => 'Payments — Dropa Manage',
            'panelLabel' => 'Body Corporate',
            'nav'        => view('manage/_nav'),
            'payments'   => $payments,
            'reservationsById' => $reservationsById,
        ]);
    }

    public function approve($id)
    {
        $propertyId = auth()->user()->property_id;
        $payment    = model(PaymentModel::class)->find($id);
        $reservation = $payment ? model(ReservationModel::class)->find($payment['reservation_id']) : null;

        if ($payment === null || $reservation === null || (int) $reservation['property_id'] !== (int) $propertyId) {
            return redirect()->to('/manage/payments')->with('error', 'Payment not found.');
        }

        (new PaymentService())->approveManual((int) $id, (int) auth()->user()->id);
        $this->audit('payment.approve', 'payment', (int) $id);

        return redirect()->to('/manage/payments')->with('success', 'Payment approved.');
    }
}
