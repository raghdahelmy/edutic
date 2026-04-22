<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Http\Requests\Video\StoreVideoRequest;
use App\Http\Requests\Video\UpdateVideoRequest;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::with('section')
            ->where(function($query) {
                $query->where('scheduled_at', '<=', now())
                      ->orWhereNull('scheduled_at');
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($video) {
                $video->makeHidden('video');
                return $video;
            });

        return response()->json([
            'status' => true,
            'data'   => $videos
        ]);
    }

    public function show($id)
    {
        $video = Video::with('section')->findOrFail($id);

        if ($video->scheduled_at && $video->scheduled_at->isFuture()) {
            return response()->json([
                'status'  => false,
                'message' => 'هذا الفيديو غير متاح حالياً وسيتم نشره في: ' . $video->scheduled_at->toDateTimeString()
            ], 403);
        }

        $video->makeHidden('video');

        return response()->json([
            'status' => true,
            'data'   => $video
        ]);
    }

    public function store(StoreVideoRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('video_file')) {
            // يخزن في local (مش public) عشان مايكونش accessible مباشرة
            $path = $request->file('video_file')->store('videos', 'local');
            $data['video'] = $path;
        }

        unset($data['video_file']);
        $video = Video::create($data);
        $video->makeHidden('video');

        return response()->json([
            'status' => true,
            'message' => 'تم رفع الفيديو بنجاح.',
            'data'   => $video,
        ], 201);
    }

    public function update(UpdateVideoRequest $request, $id)
    {
        $video = Video::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('video_file')) {
            if ($video->video) {
                Storage::disk('local')->delete($video->video);
            }

            $path = $request->file('video_file')->store('videos', 'local');
            $data['video'] = $path;
        }

        unset($data['video_file']);
        $video->update($data);
        $video->makeHidden('video');

        return response()->json([
            'status' => true,
            'message' => 'تم تعديل الفيديو بنجاح.',
            'data'   => $video,
        ]);
    }

    public function destroy($id)
    {
        $video = Video::findOrFail($id);

        if ($video->video) {
            Storage::disk('local')->delete($video->video);
        }

        $video->delete();
        return response()->json(['status' => true, 'message' => 'تم الحذف بنجاح.']);
    }

    public function stream(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'يجب تسجيل الدخول.'], 401);
        }

        $video = Video::with('section.course')->findOrFail($id);

        if ($video->scheduled_at && $video->scheduled_at->isFuture()) {
            return response()->json([
                'status'  => false,
                'message' => 'هذا الفيديو غير متاح حالياً.',
            ], 403);
        }

        // الـ owner والـ admin يشوفوا بدون اشتراك
        if (!in_array($user->role, ['owner', 'admin'])) {
            $courseId = $video->section->course->id;

            $subscription = Subscription::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->where('status', 'active')
                ->first();

            if (!$subscription) {
                return response()->json([
                    'status'  => false,
                    'message' => 'يجب الاشتراك في الكورس لمشاهدة هذا الفيديو.',
                ], 403);
            }
        }

        if (!$video->video || !Storage::disk('local')->exists($video->video)) {
            return response()->json(['status' => false, 'message' => 'الفيديو غير موجود.'], 404);
        }

        $path     = Storage::disk('local')->path($video->video);
        $mimeType = mime_content_type($path);
        $size     = Storage::disk('local')->size($video->video);

        return response()->stream(function () use ($path) {
            $stream = fopen($path, 'rb');
            while (!feof($stream)) {
                echo fread($stream, 1024 * 64);
                flush();
            }
            fclose($stream);
        }, 200, [
            'Content-Type'        => $mimeType,
            'Content-Length'      => $size,
            'Content-Disposition' => 'inline',        // بيشغله مش بينزله
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control'       => 'no-store',
            'Accept-Ranges'       => 'none',           // بيمنع الـ partial download
        ]);
    }

    public function upcoming()
    {
        $videos = Video::with('section')
            ->where('scheduled_at', '>', now())
            ->get()
            ->map(function ($video) {
                $video->makeHidden('video');
                return $video;
            });

        return response()->json([
            'status' => true,
            'data'   => $videos
        ]);
    }
}
