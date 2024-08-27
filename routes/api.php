<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GooglePeopleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/auth/google', [GooglePeopleController::class, 'redirectToGoogle'])->name('google.auth');
Route::get('/auth/google/callback', [GooglePeopleController::class, 'handleGoogleCallback'])->name('google.callback');
Route::get('/google/contacts', [GooglePeopleController::class, 'index'])->name('google.people.contacts');
Route::post('/google/contacts/create', [GooglePeopleController::class, 'store'])->name('google.people.contacts.create');
Route::put('/google/contacts/update/{resourceName}', [GooglePeopleController::class, 'update'])->name('google.people.contacts.update');
Route::delete('/google/contacts/delete/{resourceName}', [GooglePeopleController::class, 'delete'])->name('google.people.contacts.delete');
