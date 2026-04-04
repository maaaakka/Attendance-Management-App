<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequestAttendance extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
{
    return [
        'work_start_datetime' => ['nullable'],
        'work_end_datetime' => ['nullable'],

        'break_start.*' => ['nullable'],
        'break_end.*' => ['nullable'],

        'note' => ['required'],
    ];
}

public function messages()
{
    return [
        'note.required' => '備考を記入してください',
    ];
}

public function withValidator($validator)
{
    $validator->after(function ($validator) {

        $start = $this->work_start_datetime;
        $end   = $this->work_end_datetime;

        // 出退勤
        if (!$start || !$end || $start >= $end) {
            $validator->errors()->add(
                'work_start_datetime',
                '出勤時間もしくは退勤時間が不適切な値です'
            );
        }

        // 休憩
        if (is_array($this->break_start)) {
            foreach ($this->break_start as $index => $bStart) {

                $bEnd = $this->break_end[$index] ?? null;

                // 両方空ならスルー
                if (!$bStart && !$bEnd) {
                    continue;
                }

                 // 前後チェック
                if ($bStart && $bEnd && $bStart >= $bEnd) {
                    $validator->errors()->add(
                        "break_start.$index",
                        '休憩時間が不適切な値です'
                    );
                }

                // 開始チェック（入力されている場合のみ）
                if ($bStart && (
                    ($start && $bStart < $start) ||
                    ($end && $bStart > $end)
                )) {
                    $validator->errors()->add(
                        "break_start.$index",
                        '休憩時間が不適切な値です'
                    );
                }

                // 終了チェック（入力されている場合のみ）
                if ($bEnd && $end && $bEnd > $end) {
                    $validator->errors()->add(
                        "break_end.$index",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
        }
    });
}
}