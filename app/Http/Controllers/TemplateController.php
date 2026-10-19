<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailTemplate;

class TemplateController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = EmailTemplate::query();

        // Selain admin hanya melihat template sesuai kategorinya
        if ($user->role !== 'admin') {
            $query->where('kategori', $user->role);
        }

        $templates = $query->latest()->get();

        return view('template_master', compact('templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_template'    => 'required',
            'jenis_monitoring' => 'required',
            'subject'          => 'required',
            'body'             => 'required',
        ]);

        // Admin bebas memilih kategori
        // Selain admin otomatis mengikuti role
        $kategori = auth()->user()->role == 'admin'
            ? $request->kategori
            : auth()->user()->role;

        EmailTemplate::create([
            'nama_template'    => $request->nama_template,
            'jenis_monitoring' => $request->jenis_monitoring,
            'kategori'         => $kategori,
            'subject'          => $request->subject,
            'body'             => $request->body,
        ]);

        return back()->with(
            'success',
            'Template berhasil dibuat'
        );
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_template'    => 'required',
            'jenis_monitoring' => 'required',
            'subject'          => 'required',
            'body'             => 'required',
        ]);

        $template = EmailTemplate::findOrFail($id);

        // Admin bebas mengubah kategori
        // Selain admin kategori tetap mengikuti role
        $kategori = auth()->user()->role == 'admin'
            ? $request->kategori
            : auth()->user()->role;

        $template->update([
            'nama_template'    => $request->nama_template,
            'jenis_monitoring' => $request->jenis_monitoring,
            'kategori'         => $kategori,
            'subject'          => $request->subject,
            'body'             => $request->body,
        ]);

        return back()->with(
            'success',
            'Template berhasil diupdate'
        );
    }
}