<?php

use App\Http\Controllers\ContractorController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DailyAttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WorkSiteCategoryController;
use App\Http\Controllers\WorkSiteController;
use App\Http\Controllers\WorkSiteCustomerController;
use App\Http\Controllers\WorkSiteItemController;
use App\Http\Controllers\WorkSitePaymentController;
use App\Http\Middleware\CheckWorkSiteAttendance;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::group(['prefix' => 'workSite'], function () {
            Route::post('create', [WorkSiteController::class, 'store'])
                ->middleware('can:workSite-create')
                ->name('workSite.create');

            Route::get('list', [WorkSiteController::class, 'list'])
                ->middleware('can:workSite-list')
                ->name('workSite.list');

            Route::get('show/{id}', [WorkSiteController::class, 'show'])
                ->middleware('can:workSite-show')
                ->name('workSite.show');

            Route::put('update/{id}', [WorkSiteController::class, 'update'])
                ->middleware('can:workSite-update')
                ->name('workSite.update');

            Route::post('close/{id}', [WorkSiteController::class, 'close'])
                ->middleware('can:workSite-close')
                ->name('workSite.close');

            Route::delete('delete/{id}', [WorkSiteController::class, 'delete'])
                ->middleware('can:workSite-delete')
                ->name('workSite.delete');

            Route::group(['prefix' => 'category'], function () {

                Route::get('/list', [WorkSiteCategoryController::class, 'list'])
                    ->middleware('can:workSite-category-list')
                    ->name('workSite.category.list');

                Route::get('/show/{id}', [WorkSiteCategoryController::class, 'show'])
                    ->middleware('can:workSite-category-show')
                    ->name('workSite.category.show');

                Route::post('/store', [WorkSiteCategoryController::class, 'store'])
                    ->middleware('can:workSite-category-create')
                    ->name('workSite.category.create');

                Route::put('/update/{id}', [WorkSiteCategoryController::class, 'update'])
                    ->middleware('can:workSite-category-update')
                    ->name('workSite.category.update');

                Route::delete('/delete/{id}', [WorkSiteCategoryController::class, 'destroy'])
                    ->middleware('can:workSite-category-delete')
                    ->name('workSite.category.delete');
            });

            Route::group(['prefix' => '{worksiteId}/payment'], function () {

                Route::post('create', [WorkSitePaymentController::class, 'create'])
                    ->middleware('can:payment-create')
                    ->name('workSite.payment.create');

                Route::get('list', [WorkSitePaymentController::class, 'list'])
                    ->middleware('can:payment-list')
                    ->name('workSite.payment.list');
                //
                //                Route::post('show/{id}', WorkSitePaymentController::class)
                //                    ->middleware('can:payment-show')
                //                    ->name('workSite.payment.show');
            });

            Route::group(['prefix' => '{worksiteId}/item'], function () {
                Route::post('/add', [WorkSiteItemController::class, 'addItems'])
                    ->middleware('can:workSite-item-add')
                    ->name('workSite.item.add');

                Route::get('/list', [WorkSiteItemController::class, 'list'])
                    ->middleware('can:workSite-item-list')
                    ->name('workSite.item.list');
                //
                //                Route::post('update/{id}', WorkSitePaymentController::class)
                //                    ->middleware('can:workSite-item-update')
                //                    ->name('workSite.item.update');
                //
                //                Route::post('show/{id}', WorkSitePaymentController::class)
                //                    ->middleware('can:workSite-item-show')
                //                    ->name('workSite.item.show');
                //
                //                Route::post('delete/{id}', WorkSitePaymentController::class)
                //                    ->middleware('can:workSite-item-delete')
                //                    ->name('workSite.item.delete');
            });

            Route::group(['prefix' => '{worksiteId}/employee'], function () {
                Route::post('assign', [WorkSiteController::class, 'assignEmployee'])
                    ->middleware('can:workSite-employee-assign')
                    ->name('workSite.employee.assign');

                //                Route::post('list', WorkSitePaymentController::class)
                //                    ->middleware('can:workSite-item-list')
                //                    ->name('workSite.item.list');
                //
                //                Route::post('update/{id}', WorkSitePaymentController::class)
                //                    ->middleware('can:workSite-item-update')
                //                    ->name('workSite.item.update');
                //
                //                Route::post('show/{id}', WorkSitePaymentController::class)
                //                    ->middleware('can:workSite-item-show')
                //                    ->name('workSite.item.show');
                //
                //                Route::post('delete/{id}', WorkSitePaymentController::class)
                //                    ->middleware('can:workSite-item-delete')
                //                    ->name('workSite.item.delete');
            });

            Route::group(['prefix' => '{worksiteId}/contractor'], function () {
                Route::put('{contractorId}/assign', [WorkSiteController::class, 'assignContractor'])
                    ->middleware('can:workSite-contractor-assign')
                    ->name('workSite.contractor.assign');

                Route::put('{contractorId}/unAssign', [WorkSiteController::class, 'unAssignContractor'])
                    ->middleware('can:workSite-contractor-assign')
                    ->name('workSite.contractor.assign');
            });

            Route::group(['prefix' => '{worksiteId}/customer'], function () {
                Route::post('/{customerId}/assign', [WorkSiteCustomerController::class, 'assignCustomer'])
                    ->middleware('can:workSite-customer-assign')
                    ->name('workSite.customer.assign');
            });
        });
        Route::group(['prefix' => 'item'], function () {
            Route::get('list', [ItemController::class, 'list'])
                ->middleware('can:item-list')
                ->name('item.list');
            Route::get('show/{id}', [ItemController::class, 'show'])
                ->middleware('can:item-show')
                ->name('item.show');
            Route::post('create', [ItemController::class, 'store'])
                ->middleware('can:item-create')
                ->name('item.create');
            Route::put('update/{id}', [ItemController::class, 'update'])
                ->middleware('can:item-update')
                ->name('item.update');
            Route::delete('delete/{id}', [ItemController::class, 'destroy'])
                ->middleware('can:item-delete')
                ->name('item.delete');

            Route::group(['prefix' => '{itemId}/category'], function () {
                Route::get('/list', [ItemCategoryController::class, 'list'])
                    ->middleware('can:item-category-list')
                    ->name('item.category.list');
                Route::get('/show/{id}', [ItemCategoryController::class, 'show'])
                    ->middleware('can:item-category-show')
                    ->name('item.category.show');
                Route::post('/create', [ItemCategoryController::class, 'store'])
                    ->middleware('can:item-category-create')
                    ->name('item.category.create');
                Route::put('/update/{id}', [ItemCategoryController::class, 'update'])
                    ->middleware('can:item-category-update')
                    ->name('item.category.update');
                Route::delete('/delete/{id}', [ItemCategoryController::class, 'destroy'])
                    ->middleware('can:item-category-delete')
                    ->name('item.category.delete');
            });
        });
        Route::group(['prefix' => 'customer'], function () {
            Route::get('/list', [CustomerController::class, 'list'])
                ->middleware('can:customer-list')
                ->name('customer.list');

            Route::get('/show/{id}', [CustomerController::class, 'show'])
                ->middleware('can:customer-show')
                ->name('customer.show');

            Route::post('/create', [CustomerController::class, 'store'])
                ->middleware('can:customer-create')
                ->name('customer.create');

            Route::put('/update/{id}', [CustomerController::class, 'update'])
                ->middleware('can:customer-update')
                ->name('customer.update');

            Route::delete('/delete/{id}', [CustomerController::class, 'destroy'])
                ->middleware('can:customer-delete')
                ->name('customer.delete');

        });
        Route::group(['prefix' => 'contractor'], function () {
            Route::get('/list', [ContractorController::class, 'list'])
                ->middleware('can:contractor-list')
                ->name('contractor.list');

            Route::get('/show/{id}', [ContractorController::class, 'show'])
                ->middleware('can:contractor-show')
                ->name('contractor.show');

            Route::post('/create', [ContractorController::class, 'store'])
                ->middleware('can:contractor-create')
                ->name('contractor.create');

            Route::put('/update/{id}', [ContractorController::class, 'update'])
                ->middleware('can:contractor-update')
                ->name('contractor.update');

            Route::delete('/delete/{id}', [ContractorController::class, 'destroy'])
                ->middleware('can:contractor-delete')
                ->name('contractor.delete');

        });
        Route::group(['prefix' => 'employee'], function () {
            Route::get('/list', [EmployeeController::class, 'list'])
                ->middleware('can:employee-list')
                ->name('employee.list');

            Route::get('/show/{id}', [EmployeeController::class, 'show'])
                ->middleware('can:employee-show')
                ->name('employee.show');

            Route::post('/create', [EmployeeController::class, 'store'])
                ->middleware('can:employee-create')
                ->name('employee.create');

            Route::put('/update/{id}', [EmployeeController::class, 'update'])
                ->middleware('can:employee-update')
                ->name('employee.update');

            Route::delete('/delete/{id}', [EmployeeController::class, 'destroy'])
                ->middleware('can:employee-delete')
                ->name('employee.delete');

            Route::group(['prefix' => '{employeeId}/daily_attendance'], function () {
                Route::post('create', [DailyAttendanceController::class, 'store'])
                    ->middleware('can:employee-attendance-add')
                    ->name('employee.dailyAttendance.add');

                Route::put('update/{dailyAttendanceId}', [DailyAttendanceController::class, 'update'])
                    ->middleware('can:employee-attendance-update')
                    ->name('employee.dailyAttendance.update');

                Route::get('list', [DailyAttendanceController::class, 'list'])
                    ->middleware('can:employee-attendance-list')
                    ->name('employee.dailyAttendance.list');
            });

        });
        Route::group(['prefix' => 'warehouse'], function () {
            Route::post('/store', [WarehouseController::class, 'store'])
                ->middleware('can:warehouse-create')
                ->name('warehouse.create');

            Route::put('/update/{warehouseId}', [WarehouseController::class, 'update'])
                ->middleware('can:warehouse-update')
                ->name('warehouse.update');

            Route::get('/list', [WarehouseController::class, 'list'])
                ->middleware('can:warehouse-list')
                ->name('warehouse.list');

            Route::get('/show/{warehouseId}', [WarehouseController::class, 'show'])
                ->middleware('can:warehouse-show')
                ->name('warehouse.show');

            Route::delete('/delete/{warehouseId}', [WarehouseController::class, 'destroy'])
                ->middleware('can:warehouse-delete')
                ->name('warehouse.delete');

            Route::group(['prefix' => '{warehouseId}/items'], function () {
                Route::post('add', [WarehouseController::class, 'addItems'])
                    ->middleware('can:warehouse-item-add')
                    ->name('warehouse.item.create');

                Route::post('move', [WarehouseController::class, 'moveItems'])
                    ->middleware('can:warehouse-item-move')
                    ->name('warehouse.item.move');

                Route::post('update', [WarehouseController::class, 'updateItems'])
                    ->middleware('can:warehouse-item-update')
                    ->name('warehouse.item.update');

                Route::post('list', [WarehouseController::class, 'listItems'])
                    ->middleware('can:warehouse-item-list')
                    ->name('warehouse.item.list');
            });
        });
        Route::group(['prefix' => 'order'], function () {
            Route::post('/create', [OrderController::class, 'store'])
                ->middleware(['can:order-create', CheckWorkSiteAttendance::class])
                ->name('order.create');
            Route::put('/update/{orderId}', [OrderController::class, 'update'])
                ->middleware('can:order-update')
                ->name('order.update');
            Route::get('/list', [OrderController::class, 'list'])
                ->middleware('can:order-list')
                ->name('order.list');
            Route::get('/show/{orderId}', [OrderController::class, 'show'])
                ->middleware('can:order-show')
                ->name('order.show');
        });
        //        Route::group(['prefix' => 'payment'], function () {
        //            Route::get('/list', [PaymentController::class, 'list'])
        //                ->middleware('can:payment-list')
        //                ->name('payment.list');
        //
        //            Route::get('/show/{id}', [PaymentController::class, 'show'])
        //                ->middleware('can:payment-show')
        //                ->name('payment.show');
        //
        //        });
    });
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', LoginController::class)->name('login');
    });
});
