<?php

class ErrorController extends AppController
{

    public function error(?int $code = 500, ?string $message = null)
    {
        $message = $message ?? "Unexpected error";

        return $this->render('error', [
            'code' => $code,
            'message' => $message
        ]);
    }
}
