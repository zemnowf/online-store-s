<?php

use Bitrix\Main\Context;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Loader;
use Bitrix\Main\Engine\Response\Component;
use Bitrix\Main\Mail\Event;
use Lib\ActionFilter\Validator;
use Bitrix\Iblock\Elements\ElementCatalogTable;


class Feedback extends CBitrixComponent implements Controllerable
{

    public function configureActions(): array
    {
        return [
            'ajaxRequest' => [
                'prefilters' => [
                    new Validator([
                        'name' => 2,
                        'surname' => 2,
                        'message' => 10,
                    ]),
                ],
            ],
        ];
    }

    public function onPrepareComponentParams($arParams): array
    {
        $arParams["EVENT_NAME"] = "FEED_FORM";
        return $arParams;
    }

    protected function listKeysSignedParameters(): array
    {
        return [
            'EMAIL_TO',
            'EVENT_MESSAGE_ID',
            'EVENT_NAME',
            'OK_TEXT',
            'IBLOCK_ID',
        ];
    }

    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }

    public function ajaxRequestAction()
    {
        $request = Context::getCurrent()->getRequest();
        $file = Context::getCurrent()->getRequest()->getFile('picture');

        $arFields = array(
            'NAME' => htmlspecialcharsEx($request['name']),
            'SURNAME' => htmlspecialcharsEx($request['surname']),
            'COMPANY' => htmlspecialcharsEx($request['company']),
            'DEPARTMENT' => htmlspecialcharsEx($request['department']),
            'MESSAGE' => htmlspecialcharsEx($request['message']),
            'FILE_PATH' => $this->saveFile($file),
        );

        $this->saveRequest($arFields);

        $this->sendMail($arFields);

        return new Component(
            "demo:response.component",
            ".default",
            array(
                'RESPONSE_MSG' => $this->arParams['OK_TEXT'],
            )
        );
    }

    private function saveRequest(array $arFields)
    {
        Loader::includeModule('iblock');
        \Bitrix\Iblock\Elements\ElementFeedbackTable::add(array(
            "NAME" => $arFields['NAME'] . ' ' . $arFields['SURNAME'],
            'ACTIVE' => 'Y',
            'DETAIL_TEXT' => $arFields['COMPANY'] . '; ' . $arFields['DEPARTMENT'] . '; ' . $arFields['MESSAGE'],
            'DETAIL_PICTURE' => CFile::MakeFileArray($arFields['FILE_PATH'])
        ));
    }

    private function saveFile(array $file)
    {
        if (move_uploaded_file($file['tmp_name'], $_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/' . $file['name'])) {
            $arFile = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/' . $file['name']);
            $arFile['MODULE_ID'] = 'iblock';
            $fileID = CFile::SaveFile($arFile, 'form');
            unlink($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/' . $file['name']);
            return CFile::GetPath($fileID);
        }
    }

    private function sendMail(array $arFields): void
    {
        $cFields = array(
            "EMAIL_TO" => $this->arParams['EMAIL_TO'],
            "NAME" => $arFields['NAME'],
            "SURNAME" => $arFields['SURNAME'],
            "COMPANY" => $arFields['COMPANY'],
            "DEPARTMENT" => $arFields['DEPARTMENT'],
            "MESSAGE" => $arFields['MESSAGE'],
        );

        $eventData = array(
            "EVENT_NAME" => $this->arParams['EVENT_NAME'],
            "LID" => SITE_ID,
            "C_FIELDS" => $cFields,
            "FILE" => array($arFields['FILE_PATH']),
        );

        if (!empty($this->arParams["EVENT_MESSAGE_ID"])) {
            foreach ($this->arParams["EVENT_MESSAGE_ID"] as $v) {
                if (intval($v) > 0) {
                    $eventData['MESSAGE_ID'] = intval($v);
                    Event::send($eventData);
                }
            }
        } else {
            Event::send($eventData);
        }
    }
}