<?php

namespace App\Http\Controllers;

use App\Enums\BillStatus;
use App\Enums\InstallmentStatus;
use App\Enums\PaymentType;
use App\Models\Bill;
use App\Models\Installment;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $lowStock = Product::query()->active()->lowStock()->orderBy('name')->limit(15)->get();
        $lowStockCount = Product::query()->active()->lowStock()->count();

        $dueInstallments = Installment::query()
            ->open()
            ->with('bill.garage')
            ->whereHas('bill', fn ($q) => $q->where('status', BillStatus::Issued->value))
            ->orderBy('due_date')
            ->limit(15)
            ->get();

        $overdueInstallments = Installment::query()
            ->due()
            ->with('bill.garage')
            ->whereHas('bill', fn ($q) => $q->where('status', BillStatus::Issued->value))
            ->orderBy('due_date')
            ->get();

        $dueCredit = Bill::query()
            ->issued()
            ->where('payment_type', PaymentType::Credit->value)
            ->whereNotNull('credit_due_date')
            ->whereRaw('(total_fils - paid_fils - credited_fils) > 0')
            ->orderBy('credit_due_date')
            ->limit(15)
            ->get();

        $todaySales = Bill::query()
            ->issued()
            ->whereDate('billed_at', now()->toDateString())
            ->get();

        return view('dashboard', [
            'lowStock' => $lowStock,
            'lowStockCount' => $lowStockCount,
            'dueInstallments' => $dueInstallments,
            'overdueInstallments' => $overdueInstallments,
            'dueCredit' => $dueCredit,
            'todayCount' => $todaySales->count(),
            'todayTotal' => $todaySales->sum('total_fils'),
            'todayCash' => $todaySales->where('payment_type', PaymentType::Cash)->sum('total_fils'),
            'todayCreditLike' => $todaySales->where('payment_type', '!=', PaymentType::Cash)->sum('total_fils'),
        ]);
    }
}
