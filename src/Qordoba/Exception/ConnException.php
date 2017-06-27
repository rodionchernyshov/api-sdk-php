<?php

namespace Qordoba\Exception;

class ConnException extends BaseException {
  const URL_NOT_PROVIDED = 1;
  const BAD_RESPONSE = 2;
}