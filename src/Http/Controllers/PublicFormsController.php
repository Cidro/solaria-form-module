<?php

namespace Asimov\Solaria\Modules\Forms\Http\Controllers;

use App;
use Asimov\Solaria\Modules\Forms\Models\Form;
use Asimov\Solaria\Modules\Forms\Models\FormResult;
use Illuminate\Http\Request;
use Solaria\Http\Controllers\Frontend\FrontendController;
use Solaria\Models\Language;
use Solaria\Models\Page;
use Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Validator;
use Event;

class PublicFormsController extends FrontendController {

    /**
     * Procesa el envio de formularios desde el sitio publico
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postProcessForm(Request $request) {
        $success_message = [];
        $language = Language::find($request->input('language_id', null));

        if (!$language)
            $language = $this->site->getDefaultLanguage();

        App::setLocale($language->code);
        setlocale(LC_ALL, $this->language->locale_code);

        /** @var Form $form */
        $form = Form::find($request->input('form_id'));

        $form_results = new FormResult();
        $form_results->form_id = $form->id;
        $form_results->assigned_user_id = $form->user->id;
        $results = $validations = $niceNames = [];

        foreach ($form->fields as $field) {
            if ($field->type == 'file') {
                $results[$field->alias] = $request->input('hidden-field-' . $field->alias, '');
            } elseif ($field->type == 'hidden' && (object_get($field->config, 'dataType') == 'json')) {
                $jsonValue = $request->input('field-' . $field->alias, '');
                if (gettype($jsonValue) == 'string')
                    $jsonValue = json_decode($jsonValue) ?: $jsonValue;
                $results[$field->alias] = $jsonValue;
            } else {
                $results[$field->alias] = $request->input('field-' . $field->alias, '');
                $validations[$field->alias] = $field->getValidations();
            }
        }

        $validator = Validator::make($results, $validations);
        foreach ($validator->errors()->toArray() as $fieldName => $error) {
            $niceNames[$fieldName] = lang('module_forms.' . $fieldName, $this->getDefaultFieldName($form->fields, $fieldName));
        }
        $validator->setAttributeNames($niceNames);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Ha ocurrido un error al procesar los datos.', 'errors' => $validator->errors()]);
            } else {
                return redirect($request->header('referer'))
                    ->withErrors($validator->errors())
                    ->withInput();
            }
        }

        $form_results->results = $results;
        $form_results->save();

        Event::fire('moduleforms.pre-send', ['form_results' => $form_results]);
        if (App::bound('moduleforms:pre-send:messages'))
            $customMessage = App::make('moduleforms:pre-send:messages');

        //Se recargan los resultados en caso de que fueran modificados por algun evento
        $form_results = FormResult::find($form_results->id);

        if ($form->user) {
            $form_results->assign($form->user);
        }

        if (isset($form->config->assignmentRules)) {
            $form_results->notifyUsers($form->config->assignmentRules);
        }

        if (object_get($form, 'config.client_email_field', null)) {
            $form_results->notifyClient($form->config->client_email_field);
        }

        //Se actualizan los resultados con la información de los usuarios
        $form_results->save();

        $success_message[] = isset($form->config->success_message) ? $form->config->success_message : 'Success';

        if ($request->session()->has('moduleforms.post-save')) {
            $success_message[] = $request->session()->get('moduleforms.post-save');
        }

        if (isset($customMessage))
            $success_message[] = $customMessage;

        if (count($success_message) == 0)
            $success_message = $success_message[0];

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $success_message, 'errors' => []]);
        } else {
            if ($redirectPageId = object_get($form->config, 'success_redirect', null)) {
                $page = Page::find($redirectPageId);
                $redirectTo = $page->getUrl();
            } else {
                $redirectTo = $request->header('referer');
            }
            return response()->redirectTo($redirectTo)->with('success', $success_message);
        }
    }

    public function postProcessFileUpload(Request $request) {
        $form = Form::find($request->input('form_id'));
        $files = $response = $validations = $niceNames = [];

        foreach ($form->fields as $field) {
            if ($field->type == 'file') {
                $files[$field->alias] = $request->file('field-' . $field->alias, '');
                $validations[$field->alias] = $field->getValidations();
                $errors = $files[$field->alias]->getError() > 0 ? [$files[$field->alias]->getErrorMessage()] : [];

                $fileName = $files[$field->alias]->getClientOriginalName();
                $fileCount = 0;
                if (!$errors) {
                    while (Storage::drive('modules')->exists('moduleforms/' . $fileName)) {
                        $fileName = $this->addCountToFilename($files[$field->alias], (++$fileCount));
                    }
                    Storage::drive('modules')->put(
                        'moduleforms/' . $fileName,
                        file_get_contents($files[$field->alias]->getRealPath())
                    );
                }

                $response[$field->alias] = [
                    'name' => $fileName,
                    'size' => $files[$field->alias]->getSize(),
                    'error' => $errors,
                    'input' => $field->alias
                ];
            }
        }

        $validator = Validator::make($files, $validations);
        if ($validator->fails()) {
            foreach ($files as $inputName => $file) {
                $response[$inputName]['error'] += $validator->errors()->get($inputName);
            }
        }

        return response()->json(['files' => array_values($response)]);
    }

    protected function getDefaultFieldName($fields, $fieldName) {
        foreach ($fields as $field) {
            if ($field->alias == $fieldName)
                return $field->name;
        }
    }

    /**
     * @param UploadedFile $file
     * @param $count
     * @return string
     */
    protected function addCountToFilename($file, $count) {
        $extension = '.' . $file->getClientOriginalExtension();
        $fileName = rtrim($file->getClientOriginalName(), $extension);
        return $fileName . ' (' . $count . ')' . $extension;
    }
}