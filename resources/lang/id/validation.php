<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Atribut :attribute harus diterima.',
    'accepted_if' => 'Atribut :attribute harus diterima ketika :other adalah :value.',
    'active_url' => 'Atribut :attribute bukan URL yang valid.',
    'after' => 'Atribut :attribute harus tanggal setelah :date.',
    'after_or_equal' => 'Atribut :attribute harus tanggal setelah atau sama dengan :date.',
    'alpha' => 'Atribut :attribute hanya boleh berisi huruf.',
    'alpha_dash' => 'Atribut :attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num' => 'Atribut :attribute hanya boleh berisi huruf dan angka.',
    'array' => 'Atribut :attribute harus berupa array.',
    'before' => 'Atribut :attribute harus tanggal sebelum :date.',
    'before_or_equal' => 'Atribut :attribute harus tanggal sebelum atau sama dengan :date.',
    'between' => [
        'numeric' => 'Atribut :attribute harus antara :min dan :max.',
        'file' => 'Atribut :attribute harus antara :min dan :max kilobita.',
        'string' => 'Atribut :attribute harus antara :min dan :max karakter.',
        'array' => 'Atribut :attribute harus memiliki antara :min dan :max item.',
    ],
    'boolean' => 'Atribut :attribute harus berupa true atau false.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Kata sandi salah.',
    'date' => 'Atribut :attribute bukan tanggal yang valid.',
    'date_equals' => 'Atribut :attribute harus tanggal yang sama dengan :date.',
    'date_format' => 'Format :attribute tidak cocok dengan :format.',
    'declined' => 'Atribut :attribute harus ditolak.',
    'declined_if' => 'Atribut :attribute harus ditolak ketika :other adalah :value.',
    'different' => 'Atribut :attribute dan :other harus berbeda.',
    'digits' => 'Atribut :attribute harus berisi :digits digit.',
    'digits_between' => 'Atribut :attribute harus antara :min dan :max digit.',
    'dimensions' => 'Atribut :attribute memiliki dimensi gambar yang tidak valid.',
    'distinct' => 'Bidang :attribute memiliki nilai duplikat.',
    'email' => 'Atribut :attribute harus alamat surel yang valid.',
    'ends_with' => 'Atribut :attribute harus diakhiri dengan salah satu dari: :values.',
    'enum' => 'Pilihan :attribute yang dipilih tidak valid.',
    'exists' => 'Pilihan :attribute yang dipilih tidak valid.',
    'file' => 'Atribut :attribute harus berupa berkas.',
    'filled' => 'Bidang :attribute harus memiliki nilai.',
    'gt' => [
        'numeric' => 'Atribut :attribute harus lebih besar dari :value.',
        'file' => 'Atribut :attribute harus lebih besar dari :value kilobita.',
        'string' => 'Atribut :attribute harus lebih besar dari :value karakter.',
        'array' => 'Atribut :attribute harus memiliki lebih dari :value item.',
    ],
    'gte' => [
        'numeric' => 'Atribut :attribute harus lebih besar dari atau sama dengan :value.',
        'file' => 'Atribut :attribute harus lebih besar dari atau sama dengan :value kilobita.',
        'string' => 'Atribut :attribute harus lebih besar dari atau sama dengan :value karakter.',
        'array' => 'Atribut :attribute harus memiliki :value item atau lebih.',
    ],
    'image' => 'Atribut :attribute harus berupa gambar.',
    'in' => 'Pilihan :attribute yang dipilih tidak valid.',
    'in_array' => 'Bidang :attribute tidak ada di :other.',
    'integer' => 'Atribut :attribute harus bilangan bulat.',
    'ip' => 'Atribut :attribute harus alamat IP yang valid.',
    'ipv4' => 'Atribut :attribute harus alamat IPv4 yang valid.',
    'ipv6' => 'Atribut :attribute harus alamat IPv6 yang valid.',
    'mac_address' => 'Atribut :attribute harus alamat MAC yang valid.',
    'json' => 'Atribut :attribute harus string JSON yang valid.',
    'lt' => [
        'numeric' => 'Atribut :attribute harus kurang dari :value.',
        'file' => 'Atribut :attribute harus kurang dari :value kilobita.',
        'string' => 'Atribut :attribute harus kurang dari :value karakter.',
        'array' => 'Atribut :attribute harus memiliki kurang dari :value item.',
    ],
    'lte' => [
        'numeric' => 'Atribut :attribute harus kurang dari atau sama dengan :value.',
        'file' => 'Atribut :attribute harus kurang dari atau sama dengan :value kilobita.',
        'string' => 'Atribut :attribute harus kurang dari atau sama dengan :value karakter.',
        'array' => 'Atribut :attribute tidak boleh memiliki lebih dari :value item.',
    ],
    'max' => [
        'numeric' => 'Atribut :attribute tidak boleh lebih besar dari :max.',
        'file' => 'Atribut :attribute tidak boleh lebih besar dari :max kilobita.',
        'string' => 'Atribut :attribute tidak boleh lebih besar dari :max karakter.',
        'array' => 'Atribut :attribute tidak boleh memiliki lebih dari :max item.',
    ],
    'mimes' => 'Atribut :attribute harus berupa berkas dengan tipe: :values.',
    'mimetypes' => 'Atribut :attribute harus berupa berkas dengan tipe: :values.',
    'min' => [
        'numeric' => 'Atribut :attribute harus minimal :min.',
        'file' => 'Atribut :attribute harus minimal :min kilobita.',
        'string' => 'Atribut :attribute harus minimal :min karakter.',
        'array' => 'Atribut :attribute harus memiliki minimal :min item.',
    ],
    'multiple_of' => 'Atribut :attribute harus merupakan kelipatan dari :value.',
    'not_in' => 'Pilihan :attribute yang dipilih tidak valid.',
    'not_regex' => 'Format :attribute tidak valid.',
    'numeric' => 'Atribut :attribute harus berupa angka.',
    'password' => 'Kata sandi salah.',
    'present' => 'Bidang :attribute harus ada.',
    'prohibited' => 'Bidang :attribute dilarang.',
    'prohibited_if' => 'Bidang :attribute dilarang ketika :other adalah :value.',
    'prohibited_unless' => 'Bidang :attribute dilarang kecuali :other ada dalam :values.',
    'prohibits' => 'Bidang :attribute melarang :other untuk ada.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => 'Bidang :attribute wajib diisi.',
    'required_if' => 'Bidang :attribute wajib diisi ketika :other adalah :value.',
    'required_unless' => 'Bidang :attribute wajib diisi kecuali :other ada dalam :values.',
    'required_with' => 'Bidang :attribute wajib diisi ketika :values ada.',
    'required_with_all' => 'Bidang :attribute wajib diisi ketika :values ada.',
    'required_without' => 'Bidang :attribute wajib diisi ketika :values tidak ada.',
    'required_without_all' => 'Bidang :attribute wajib diisi ketika tidak ada :values.',
    'same' => 'Atribut :attribute dan :other harus cocok.',
    'size' => [
        'numeric' => 'Atribut :attribute harus berukuran :size.',
        'file' => 'Atribut :attribute harus berukuran :size kilobita.',
        'string' => 'Atribut :attribute harus berukuran :size karakter.',
        'array' => 'Atribut :attribute harus mengandung :size item.',
    ],
    'starts_with' => 'Atribut :attribute harus dimulai dengan salah satu dari: :values.',
    'string' => 'Atribut :attribute harus berupa string.',
    'timezone' => 'Atribut :attribute harus zona waktu yang valid.',
    'unique' => 'Atribut :attribute sudah ada sebelumnya.',
    'uploaded' => 'Gagal mengunggah :attribute.',
    'url' => 'Atribut :attribute harus URL yang valid.',
    'uuid' => 'Atribut :attribute harus UUID yang valid.',



    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
