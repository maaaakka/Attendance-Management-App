<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
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
            'work_start_datetime' => ['required'],
            'work_end_datetime' => ['required'],

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

            $start = strtotime($this->work_start_datetime);
            $end = strtotime($this->work_end_datetime);

            // 出勤退勤チェック
            if ($start >= $end) {
                $validator->errors()->add(
                    'work_start_datetime',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            $breakStarts = $this->break_start;
            $breakEnds = $this->break_end;

            if ($breakStarts) {

                foreach ($breakStarts as $index => $breakStart) {

                    if (!$breakStart || !$breakEnds[$index]) {
                        continue;
                    }

                    $bs = strtotime($breakStart);
                    $be = strtotime($breakEnds[$index]);

                    // 休憩開始チェック
                    if ($bs < $start || $bs > $end) {
                        $validator->errors()->add(
                            'break_start',
                            '休憩時間が不適切な値です'
                        );
                    }

                    // 休憩終了チェック
                    if ($be > $end) {
                        $validator->errors()->add(
                            'break_end',
                            '休憩時間もしくは退勤時間が不適切な値です'
                        );
                    }
                }
            }

        });
    }
}
