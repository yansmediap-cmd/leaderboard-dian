<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpkRequest extends FormRequest
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
        return [
            'kode_dealer' => ['required', 'string', 'max:20'],
            'kode_sales' => ['required', 'string', 'max:30'],
            'no_spk' => ['required', 'string', 'max:50'],
            'tipe_motor' => ['required', 'string', 'max:100'],
            'tanggal_spk' => ['required', 'date'],
            'jumlah_unit' => ['required', 'integer', 'min:1'],
            'harga_unit' => ['required', 'numeric', 'min:0'],
        ];
    }
}
