<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToCollection, WithHeadingRow, WithValidation
{
    public $errors = [];
    public $successCount = 0;
    public $errorCount = 0;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            try {
                // Skip empty rows
                if (empty($row['name']) && empty($row['email'])) {
                    continue;
                }

                $validator = Validator::make($row->toArray(), $this->rules(), $this->customValidationMessages());

                if ($validator->fails()) {
                    $this->errors[] = [
                        'row' => $row,
                        'errors' => $validator->errors()->all()
                    ];
                    $this->errorCount++;
                    continue;
                }

                // Create user
                User::create([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => Hash::make($row['password'] ?? 'password123'),
                    'is_approved' => $row['is_approved'] ?? false,
                ]);

                $this->successCount++;

            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $row,
                    'errors' => ['Error: ' . $e->getMessage()]
                ];
                $this->errorCount++;
            }
        }
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:8',
            'is_approved' => 'nullable|boolean',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.min' => 'Password minimal 8 karakter.',
            'is_approved.boolean' => 'Status persetujuan harus benar atau salah.',
        ];
    }

    /**
     * Get import results
     */
    public function getResults(): array
    {
        return [
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'errors' => $this->errors,
        ];
    }
}
