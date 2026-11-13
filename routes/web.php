<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SimplePermissionController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ScraperController;
use App\Http\Controllers\DashboardController;

Route::get('/', fn() => redirect()->route('login'));
Route::middleware('guest')->group(function () {


Route::get('/', fn() => redirect()->route('login'));
    // Login
    Route::get('/login', [
        LoginController::class,
        'showLoginForm'
    ])->name('login');

    Route::post('/login', [
        LoginController::class,
        'login'
    ])->name('login.submit');


    // Register
    Route::get('/register', [
        RegisterController::class,
        'showRegistrationForm'
    ])->name('register');

    Route::post('/register', [
        RegisterController::class,
        'register'
    ])->name('register.submit');
});


Route::middleware(['auth', 'nocache'])->group(function () {


Route::prefix('users1')->name('users1.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/data', [UserController::class, 'data'])->name('data');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
     Route::get('/{id}/view', [UserController::class, 'view'])->name('view');
    Route::put('/{id}', [UserController::class, 'update'])->name('update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
});

    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RolePermissionController::class, 'index'])->name('index');
        Route::get('/data', [RolePermissionController::class, 'data'])->name('data');
        Route::post('/', [RolePermissionController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [RolePermissionController::class, 'edit'])->name('edit');
        Route::put('/{id}', [RolePermissionController::class, 'update'])->name('update');
        Route::delete('/{id}', [RolePermissionController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('profiles')->name('profiles.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/data', [ProfileController::class, 'data'])->name('data');
        Route::post('/', [ProfileController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::get('/{id}/show', [ProfileController::class, 'show'])->name('show');
        Route::post('/{id}/update', [ProfileController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProfileController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('organizations')->name('organizations.')->group(function () {
        Route::get('/', [OrganizationController::class, 'index'])->name('index');
        Route::get('/data', [OrganizationController::class, 'data'])->name('data');
        Route::post('/', [OrganizationController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [OrganizationController::class, 'edit'])->name('edit');
        Route::get('/{id}/show', [OrganizationController::class, 'show'])->name('show');
        Route::post('/{id}/update', [OrganizationController::class, 'update'])->name('update');
        Route::delete('/{id}', [OrganizationController::class, 'destroy'])->name('destroy');
    });

    // =========================================================================
    // Web Scraper (HTML) & Static PDF Scraper Management
    // Web Scraper operates on ScrapedRecord (table: scraped_records)
    // Static PDF Scraper operates on PdfScrapedRecord (table: pdf_scraped_records)
    // =========================================================================
    Route::prefix('scraper')->name('scraper.')->group(function () {
        // --- Static PDF Scraper Endpoints (Dedicated prefix for pdf_scraped_records) ---
        Route::get('/pdf', [ScraperController::class, 'pdfIndex'])->name('pdf');
        Route::prefix('pdf')->name('pdf.')->group(function () {
            Route::get('/data', [ScraperController::class, 'pdfData'])->name('data');
            Route::post('/run', [ScraperController::class, 'runPdf'])->name('run');
            Route::post('/bulk-delete', [ScraperController::class, 'pdfBulkDelete'])->name('bulk-delete');
            Route::get('/{id}/show', [ScraperController::class, 'pdfShow'])->whereNumber('id')->name('show');
            Route::get('/{id}/edit', [ScraperController::class, 'pdfEdit'])->whereNumber('id')->name('edit');
            Route::post('/{id}/update', [ScraperController::class, 'pdfUpdate'])->whereNumber('id')->name('update');
            Route::delete('/{id}', [ScraperController::class, 'pdfDestroy'])->whereNumber('id')->name('destroy');
            Route::post('/{id}/convert-profile', [ScraperController::class, 'pdfConvertProfile'])->whereNumber('id')->name('convert-profile');
            Route::post('/{id}/convert-org', [ScraperController::class, 'pdfConvertOrg'])->whereNumber('id')->name('convert-org');
        });

        // --- Web Scraper (HTML) Endpoints (Operates on scraped_records) ---
        Route::get('/', [ScraperController::class, 'index'])->name('index');
        Route::get('/data', [ScraperController::class, 'data'])->name('data');
        Route::post('/run', [ScraperController::class, 'run'])->name('run');
        Route::post('/bulk-delete', [ScraperController::class, 'bulkDelete'])->name('bulk-delete');
        Route::get('/{id}/show', [ScraperController::class, 'show'])->whereNumber('id')->name('show');
        Route::get('/{id}/edit', [ScraperController::class, 'edit'])->whereNumber('id')->name('edit');
        Route::post('/{id}/update', [ScraperController::class, 'update'])->whereNumber('id')->name('update');
        Route::delete('/{id}', [ScraperController::class, 'destroy'])->whereNumber('id')->name('destroy');
        Route::post('/{id}/convert-profile', [ScraperController::class, 'convertProfile'])->whereNumber('id')->name('convert-profile');
        Route::post('/{id}/convert-org', [ScraperController::class, 'convertOrg'])->whereNumber('id')->name('convert-org');
    });




    // Direct Permission Assignments for Users
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/', [SimplePermissionController::class, 'index'])->name('index');
        Route::get('data', [SimplePermissionController::class, 'data'])->name('data');
        Route::post('store', [SimplePermissionController::class, 'store'])->name('store');
        Route::get('{id}/edit', [SimplePermissionController::class, 'edit'])->whereNumber('id')->name('edit');
        Route::post('{id}/update', [SimplePermissionController::class, 'update'])->whereNumber('id')->name('update');
        Route::post('{id}/delete', [SimplePermissionController::class, 'destroy'])->whereNumber('id')->name('destroy');
        Route::get('{id}/permissions', [SimplePermissionController::class, 'permissions'])->whereNumber('id')->name('permissions');
        Route::post('{id}/permissions', [SimplePermissionController::class, 'savePermissions'])->whereNumber('id')->name('permissions.save');
    });

    // Backward-compatibility alias for users.*
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [SimplePermissionController::class, 'index'])->name('index');
        Route::get('data', [SimplePermissionController::class, 'data'])->name('data');
        Route::post('store', [SimplePermissionController::class, 'store'])->name('store');
        Route::get('{id}/edit', [SimplePermissionController::class, 'edit'])->whereNumber('id')->name('edit');
        Route::post('{id}/update', [SimplePermissionController::class, 'update'])->whereNumber('id')->name('update');
        Route::post('{id}/delete', [SimplePermissionController::class, 'destroy'])->whereNumber('id')->name('destroy');
        Route::get('{id}/permissions', [SimplePermissionController::class, 'permissions'])->whereNumber('id')->name('permissions');
        Route::post('{id}/permissions', [SimplePermissionController::class, 'savePermissions'])->whereNumber('id')->name('permissions.save');
    });

    // Dedicated Permissions Management under Modules
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::get('data', [PermissionController::class, 'data'])->name('data');
        Route::post('/', [PermissionController::class, 'store'])->name('store');
        Route::get('{id}/edit', [PermissionController::class, 'edit'])->whereNumber('id')->name('edit');
        Route::put('{id}', [PermissionController::class, 'update'])->whereNumber('id')->name('update');
        Route::post('{id}/update', [PermissionController::class, 'update'])->whereNumber('id')->name('update.post');
        Route::delete('{id}', [PermissionController::class, 'destroy'])->whereNumber('id')->name('destroy');
        Route::post('{id}/delete', [PermissionController::class, 'destroy'])->whereNumber('id')->name('destroy.post');
    });

Route::get('/reqres', function () {
    return view('reqres');
})->name('reqres.view');



    Route::post('/logout', [
        LoginController::class,
        'logout'
    ])->name('logout');

  //  Route::get('/dashboard', function () {
 //       return view('admin.dashboard');
 //   })->name('dashboard');


 //       Route::get('/dashboard1old', function () {
 //       return view('layouts_old.master1');
   // })->name('dashboard1');
 //       Route::get('/dashboard',function () {
  //      return view('layouts_new.page-template');
    //    })->name('dashboard222');

   
        Route::get('/dashboard1old', function () {
        return view('dashboard');
    })->name('dashboard123');




        
            Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');





    Route::get('/newdashboard', [DashboardController::class, 'index'])
        ->name('newdash');
    Route::get('/dashboard1', fn() => view('dashboard'))->name('dashboard');

    Route::get('/dashboardnew', fn() => view('layouts_old.master1'))->name('dashboard11');



});