<?php

class ErrorController extends AppController {

    public function error(?string $details = null)
    {
        $message = $details ?? "Unexpected error";

        return $this->render('error', [
            'msg' => $message
        ]);
    }
}