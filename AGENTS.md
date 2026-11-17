# Laravel Coding Standards & Architecture Rules

## 1. General & Style
- Gunakan PHP 8.2+ dengan strict types (`declare(strict_types=1);`) di setiap file baru.
- Ikuti standar PSR-12 dan konvensi penamaan Laravel (Controller tunggal/jamak sesuai standar, Model tunggal PascalCase, tabel jamak snake_case).
- Gunakan PHP type hinting secara eksplisit pada semua parameter dan return type method.

## 2. Arsitektur & Separation of Concerns (Sangat Penting)
- **Thin Controllers:** Controller tidak boleh berisi logika bisnis. Controller hanya bertugas menerima request, memanggil Action/Service, dan mengembalikan Response/Resource.
- **Validation:** Selalu pisahkan validasi ke dalam class `FormRequest` (`app/Http/Requests/`). Jangan gunakan `$request->validate()` di dalam Controller.
- **Business Logic:** Pindahkan logika bisnis yang kompleks ke dalam class `Action` (`app/Actions/`) atau `Service` (`app/Services/`).
- **API Response:** Gunakan `JsonResource` (`app/Http/Resources/`) untuk memformat output JSON/API, jangan kembalikan Model Eloquent secara mentah.

## 3. Database & Eloquent
- Hindari N+1 query; selalu gunakan Eager Loading (`with()`).
- Jangan gunakan `DB::raw()` jika masih bisa ditangani oleh Eloquent Query Builder.
- Wajib definisikan `$fillable` (hindari `$guarded = []`) pada setiap Model.

## 4. Clean Code Practices
- Gunakan Early Return pattern untuk menghindari `if-else` bersarang (nested conditionals).
- Batasi satu method maksimal 20-30 baris. Pecah menjadi private method jika terlalu panjang.
