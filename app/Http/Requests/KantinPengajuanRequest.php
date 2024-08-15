<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KantinPengajuanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return match ($this->method()) {
            'POST' => $this->store(),
            'PUT' => $this->update(),
        };
    }

    public function store()
    {
        return [

            'jumlah_pengajuan' => ['required', 'integer', 'min:0'],
        ];
    }

    public function update()
    {
        return [
            'alasan_penolakan' => ['nullable', 'string'],
            'status' => ['required', Rule::in('disetujui', 'ditolak')],
        ];
    }
}