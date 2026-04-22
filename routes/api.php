<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MainCategoryController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\TaskController;


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
        Route::get('/users', [AuthController::class, 'getAllUsers']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
Route::middleware('auth:sanctum')->put('/users/{id}/status', [AuthController::class, 'updateStatus']);
Route::middleware('auth:sanctum')->post('/users/{id}/role', [AuthController::class, 'assignRole']);
Route::middleware('auth:sanctum')->post('/users/create', [AuthController::class, 'createUser']);
Route::middleware('auth:sanctum')->post('/profile/update', [AuthController::class, 'updateProfile']);


/*
|--------------------------------------------------------------------------
| Main Categories (الأقسام الرئيسية)
|--------------------------------------------------------------------------
*/
Route::get('main-categories', [MainCategoryController::class, 'index']);
Route::get('main-categories/{id}', [MainCategoryController::class, 'show']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/main-categories', [MainCategoryController::class, 'store']);
    Route::post('/main-categories/{id}', [MainCategoryController::class, 'update']);
    Route::delete('/main-categories/{id}', [MainCategoryController::class, 'destroy']);
});


/*
|--------------------------------------------------------------------------
| Sub Categories (الأقسام الفرعية)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/sub-categories', [SubCategoryController::class, 'store']);
    Route::put('/sub-categories/{id}', [SubCategoryController::class, 'update']);
    Route::delete('/sub-categories/{id}', [SubCategoryController::class, 'destroy']);
});

Route::get('/sub-categories', [SubCategoryController::class, 'index']);
Route::get('/sub-categories/{id}', [SubCategoryController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Courses (الكورسات)
|--------------------------------------------------------------------------
*/
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{id}', [CourseController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/courses', [CourseController::class, 'store']);
    Route::post('/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']);
    Route::get('courses-with-subscriptions', [CourseController::class, 'indexCourse']);
});


/*
|--------------------------------------------------------------------------
| Sections (الأقسام داخل الكورس)
|--------------------------------------------------------------------------
*/
Route::get('sections', [SectionController::class, 'index']);
Route::get('sections/{id}', [SectionController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('sections', [SectionController::class, 'store']);
    Route::post('sections/{id}', [SectionController::class, 'update']);
    Route::delete('sections/{id}', [SectionController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Videos (الفيديوهات)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('videos', [VideoController::class, 'index']);
    Route::post('videos', [VideoController::class, 'store']);
    Route::get('videos/{id}', [VideoController::class, 'show']);
    Route::get('videos/{id}/stream', [VideoController::class, 'stream']);
    Route::post('videos/{id}', [VideoController::class, 'update']);
    Route::delete('videos/{id}', [VideoController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Subscriptions (الاشتراكات)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('subscriptions', [SubscriptionController::class, 'index']);
    Route::get('subscriptions/{id}', [SubscriptionController::class, 'show']);
    Route::post('subscriptions', [SubscriptionController::class, 'store']);
    Route::get('my-subscriptions', [SubscriptionController::class, 'mySubscriptions']);
    Route::post('subscriptions/{id}/receipt', [SubscriptionController::class, 'uploadReceipt']);
    Route::put('subscriptions/{id}/status', [SubscriptionController::class, 'updateSubscriptionStatus']);

    Route::delete('subscriptions/{id}', [SubscriptionController::class, 'destroy']);
});
/*
|--------------------------------------------------------------------------
| Blogs (المدونة)
|--------------------------------------------------------------------------
*/
Route::get('blogs', [BlogController::class, 'index']);
Route::get('blogs/{id}', [BlogController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('blogs', [BlogController::class, 'store']);
    Route::post('blogs/{id}', [BlogController::class, 'update']);
    Route::delete('blogs/{id}', [BlogController::class, 'destroy']);
    Route::get('admin/blogs', [BlogController::class, 'indexAdmin']);

});

/*
|--------------------------------------------------------------------------
| Comments (التعليقات)
|--------------------------------------------------------------------------
*/
Route::get('comments', [CommentController::class, 'index']);
Route::get('comments/{id}', [CommentController::class, 'show']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('comments', [CommentController::class, 'store']);
    Route::post('comments/{id}', [CommentController::class, 'update']);
    Route::delete('comments/{id}', [CommentController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Replies (الردود على التعليقات)
|--------------------------------------------------------------------------
*/
Route::get('replies', [ReplyController::class, 'index']);
Route::get('replies/{id}', [ReplyController::class, 'show']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('replies', [ReplyController::class, 'store']);
    Route::post('replies/{id}', [ReplyController::class, 'update']);
    Route::delete('replies/{id}', [ReplyController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Contact (تواصل معنا)
|--------------------------------------------------------------------------
*/
Route::post('/contacts', [ContactController::class, 'store']); // إرسال رسالة
Route::get('/contacts', [ContactController::class, 'index'])->middleware('auth:sanctum'); // عرض كل الرسائل (للأدمن)
Route::get('/contacts/{id}', [ContactController::class, 'show'])->middleware('auth:sanctum');
Route::delete('/contacts/{id}', [ContactController::class, 'destroy'])->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Group (مجموعات التدريب)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('groups')->group(function () {
    Route::get('/', [GroupController::class, 'index']);
    Route::post('/', [GroupController::class, 'store']);
    Route::post('{groupId}/rate', [GroupController::class, 'rateGroup']);
    Route::put('{groupId}/member/{userId}', [GroupController::class, 'updateMember']);
    Route::get('{id}', [GroupController::class, 'show']); 
    Route::put('{id}', [GroupController::class, 'update']);
    Route::delete('{id}', [GroupController::class, 'destroy']);

});


/*
|--------------------------------------------------------------------------
| Reviews (التقييمات)
|--------------------------------------------------------------------------
*/
Route::controller(ReviewController::class)->prefix('reviews')->group(function () {
    Route::get('/', 'index');
    Route::get('/{id}', 'show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', 'store');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });
});




/*
|--------------------------------------------------------------------------
| Task (التاسكات)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // كل عمليات CRUD الأساسية (index, show, store, update, destroy)
    Route::apiResource('tasks', TaskController::class);

    //  فانكشن إضافية خاصة بالطلبة (تحديث الإجابة)
    Route::post('tasks/{task}/answer', [TaskController::class, 'updateAnswer']);
});



