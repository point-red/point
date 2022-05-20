<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendEmailRequest extends FormRequest
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
        /*
         * TODO to, cc, bcc can be an array of email
         * Only need to tweak the rules here
         * The Mail library supports it
         */
        return [
            'to' => 'required|email',
            'cc' => 'email',
            'bcc' => 'email',
            'reply_to' => 'required_with:reply_to_name|email',
            'reply_to_name' => 'string',
            'subject' => 'required|filled|string',
            'body' => 'string',
            'attachments' => 'array',

            // TODO support more type, separated by comma
            'attachments.*.type' => 'required|in:pdf',
            'attachments.*.filename' => 'string',
            // 'attachments.*.html' => 'required_if:attachments.*.type,pdf',
            'attachments.*.orientation' => 'in:portrait,landscape',

            // TODO add more paper size https://github.com/dompdf/dompdf/blob/d30679a47a067a69540c988405cb675404898acc/src/Adapter/CPDF.php#L45
            'attachments.*.paper' => 'in:a4',
        ];
    }
}
