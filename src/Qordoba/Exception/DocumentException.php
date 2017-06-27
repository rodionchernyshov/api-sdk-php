<?php

namespace Qordoba\Exception;

class DocumentException extends BaseException {
  const TRANSLATION_STRING_EXISTS = 1;
  const TRANSLATION_STRING_NOT_EXISTS = 2;
}