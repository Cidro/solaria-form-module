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

class PublicFormsController extends FrontendController {

    /**
     * Procesa el envio de formularios desde el sitio publico
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postProcessForm(Request $request) {
        $language = Language::find($request->input('language_id', null));

        if (!$language)
            $language = $this->site->getDefaultLanguage();

        App::setLocale($language->code);
        setlocale(LC_ALL, $this->language->locale_code);

        /** @var Form $form */
        $form = Form::find($request->input('form_id'));
        $validator = $form->validate($request);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Ha ocurrido un error al procesar los datos.', 'errors' => $validator->errors()]);
            } else {
                return redirect($request->header('referer'))
                    ->withErrors($validator->errors())
                    ->withInput();
            }
        }

        $formResults = $form->saveResults($validator->getData());
        $customMessage = $form->triggerEvent('pre-send', $formResults);

        //Se recargan los resultados en caso de que fueran modificados por algun evento
        $formResults = FormResult::find($formResults->id);

        $form->sendNotifications($formResults);
        $successMessage = $form->prepareMessage($request, $customMessage);

        $redirectTo = $this->getSuccessRedirect($form->config, $request);
        if ($request->ajax()) {
            if($redirectTo != $request->header('referer')) {
                $request->session()->flash('success', $successMessage);
                return response()->json(['success' => true, 'message' => $successMessage, 'errors' => [], 'redirect' => $redirectTo]);
            } else {
                return response()->json(['success' => true, 'message' => $successMessage, 'errors' => []]);
            }
        } else {
            return response()->redirectTo($redirectTo)->with('success', $successMessage);
        }
    }

    public function postProcessFileUpload(Request $request) {
        $form = Form::find($request->input('form_id'));
        $files = $response = $validations = $niceNames = [];

        foreach ($form->fields as $field) {
            if ($field->type == 'file' && in_array('field-' . $field->alias, $request->files->keys())) {
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

    /**
     * @param $formConfig
     * @param $request
     * @return string
     */
    protected function getSuccessRedirect($formConfig, Request $request) {
        $redirectTo = $request->header('referer');

        if($customSuccessRedirect = $request->input('success_redirect', null)){
            if(is_numeric($customSuccessRedirect)) {
                $page = Page::find($customSuccessRedirect);
                $redirectTo = $page->getUrl();
            } else {
                $redirectTo = $customSuccessRedirect;
            }
        } else {
            if ($redirectPageId = object_get($formConfig, 'success_redirect', null)) {
                $page = Page::find($redirectPageId);
                $redirectTo = $page->getUrl();
            }
        }

        return $redirectTo;
    }
}