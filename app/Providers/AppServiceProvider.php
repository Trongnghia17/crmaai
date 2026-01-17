<?php

namespace App\Providers;

use App\Repositories\Category\CategoryRepository;
use App\Repositories\Category\CategoryRepositoryInterfae;
use App\Repositories\Customer\CustomerRepository;
use App\Repositories\Customer\CustomerRepositoryInterface;
use App\Repositories\Dashboard\DashBoardRepository;
use App\Repositories\Dashboard\DashboardRepositoryInterface;
use App\Repositories\Employee\EmployeeRepositoriesInterface;
use App\Repositories\Employee\EmployeeRepository;
use App\Repositories\Inventory\InventoryRepository;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\InventoryDetail\InventoryDetailRepository;
use App\Repositories\InventoryDetail\InventoryDetailRepositoryInterface;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\OrderDetail\OrderDetailRepository;
use App\Repositories\OrderDetail\OrderDetailRepositoryInterface;
use App\Repositories\Product\ProductRepository;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\ProductStorage\ProductStorageRepository;
use App\Repositories\ProductStorage\ProductStorageRepositoryInterface;
use App\Repositories\ProfitLoss\ProfitLossRepository;
use App\Repositories\ProfitLoss\ProfitLossRepositoryInterface;
use App\Repositories\ReceiptPayment\ReceiptPaymentRepositoryInterface;
use App\Repositories\ReceiptPayment\ReceiptPaymentRepository;
use App\Repositories\Report\ReportRepository;
use App\Repositories\Report\ReportRepositoryInterface;
use App\Repositories\Supplier\SupplierRepository;
use App\Repositories\Supplier\SupplierRepositoryInterface;
use App\Repositories\UserRepository\UserRepository;
use App\Repositories\UserRepository\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            CustomerRepositoryInterface::class,
            CustomerRepository::class
        );
        $this->app->singleton(
            OrderRepositoryInterface::class,
            OrderRepository::class
        );
        $this->app->singleton(
            SupplierRepositoryInterface::class,
            SupplierRepository::class
        );
        $this->app->singleton(
            OrderDetailRepositoryInterface::class,
            OrderDetailRepository::class
        );
        $this->app->singleton(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );
        $this->app->singleton(
            ProductStorageRepositoryInterface::class,
            ProductStorageRepository::class
        );

        $this->app->singleton(
            ReceiptPaymentRepositoryInterface::class,
            ReceiptPaymentRepository::class
        );

        $this->app->singleton(
            ReportRepositoryInterface::class,
            ReportRepository::class
        );
        $this->app->singleton(
            InventoryRepositoryInterface::class,
            InventoryRepository::class
        );
        $this->app->singleton(
            InventoryDetailRepositoryInterface::class,
            InventoryDetailRepository::class
        );
        $this->app->singleton(
            CategoryRepositoryInterfae::class,
            CategoryRepository::class
        );

        $this->app->singleton(
            EmployeeRepositoriesInterface::class,
            EmployeeRepository::class
        );
        $this->app->singleton(
            UserRepositoryInterface::class,
            UserRepository::class
        );
        $this->app->singleton(
            ProfitLossRepositoryInterface::class,
            ProfitLossRepository::class
        );
        $this->app->singleton(
            DashboardRepositoryInterface::class,
            DashboardRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
