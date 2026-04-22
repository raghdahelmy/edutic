<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * ✅ عرض جميع الرسائل (خاص بالأدمن)
     */
    public function index()
    {
        $contacts = Contact::latest()->get();

        return response()->json([
            'status'  => true,
            'message' => 'تم جلب جميع الرسائل بنجاح',
            'data'    => $contacts,
        ]);
    }

    /**
     * ✅ حفظ رسالة جديدة
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'phone'   => 'required|string|max:20',
            'message' => 'required|string',
        ]);

        $contact = Contact::create([
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'message' => $request->message,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'تم إرسال الرسالة بنجاح، سيتم التواصل معك قريبًا.',
            'data'    => $contact,
        ], 201);
    }

    /**
     * ✅ عرض رسالة محددة
     */
    public function show($id)
    {
        $contact = Contact::findOrFail($id);

        return response()->json([
            'status'  => true,
            'message' => 'تم جلب تفاصيل الرسالة بنجاح',
            'data'    => $contact,
        ]);
    }

    /**
     * ✅ حذف رسالة
     */
    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف الرسالة بنجاح',
        ]);
    }
}
