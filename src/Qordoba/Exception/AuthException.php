<?php

namespace Qordoba\Exception;

class AuthException extends BaseException {
  const USERNAME_NOT_PROVIDED = 1;
  const PASSWORD_NOT_PROVIDED = 2;
}