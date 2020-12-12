<?php

require_once './express-php/db/db.php';
require_once './express-php/uuid.php';

class User
{
  static function exists($username, $email)
  {
    $repository = new Model("users");

    $rows = $repository
      ->Select("username, email, id")
      ->Where(['username=' => $username])
      ->Or(['email=' => $email])
      ->Limit(1)
      ->Do();
    return gettype($rows) === 'array' && count($rows) > 0;
  }

  /**
   * returns user by id
   * 
   * returns null if not found
   */
  static function getById($id)
  {
    $repository = new Model("users");
    $rows = $repository
      ->Select("username, email, id")
      ->Where(['id=' => $id])
      ->Limit(1)
      ->Do();
    if (gettype($rows) != 'array' || count($rows) == 0) {
      return null;
    }
    return $rows[0];
  }

  static function confirmUser($email, $token)
  {
    $repository = new Model("users");
    $rows = $repository
      ->Select("username, confirmation_token, verified")
      ->Where(['email=' => $email])
      ->Limit(1)
      ->Do();
    if (gettype($rows) != 'array' || count($rows) == 0) {
      return false;
    }
    $user = $rows[0];
    if ($user['confirmation_token'] !== $token || $user['verified'] !== 0) {

      return false;
    }
    unset($user['confirmation_token']);
    $user['verified'] = true;
    $update = new Model('users');
    $result = $update
      ->Update()
      ->Where(['email=' => $email])
      ->Set(['verified' => 1, 'confirmation_token' => ''])
      ->Do();

    if (!$result) {

      return false;
    }

    return $user;
  }

  /**
   * Create user creates a user and returns the value of the verification token
   * 
   * returns false if not successful
   */
  static function createUser($username, $password, $email)
  {
    $repository = new Model("users");
    $id = UUID::v4();
    $confirmationToken = UUID::v4();
    $salt = random_bytes(24);
    $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['salt' => $salt]);
    if ($repository->Create([
      'username' => $username,
      'salt' => $salt,
      'password' => $passwordHash,
      'verified' => 0,
      'confirmation_token' => $confirmationToken,
      'email' => $email,
      'id' => $id,
    ])->Do() == false) {
      return false;
    }
    return $confirmationToken;
  }

  /**
   * Verifies user's mail
   */
  static function verifyUser($email, $verificationToken)
  {
  }

  /**
   * returns the loged user. if failed returns null.
   */
  static function logUser($email, $password)
  {
  }
}
