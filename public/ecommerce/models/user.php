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

  static function checkPassword($email, $password)
  {
    $repository = new Model("users");
    $rows = $repository
      ->Select("email, verified, salt, password, id, username")
      ->Where(['email=' => $email])
      ->Limit(1)
      ->Do();
    if (gettype($rows) != 'array' || count($rows) == 0) {
      return false;
    }
    $user = $rows[0];
    if (!$user['verified']) {
      return false;
    }
    $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['salt' => $user['salt']]);
    if ($passwordHash !== $user['password']) {
      return false;
    }
    return ['email' => $user['email'], 'username' => $user['username'], 'id' => $user['id']];
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
      ->Select("username, email, id, admin")
      ->Where(['id=' => $id])
      ->Limit(1)
      ->Do();
    if (gettype($rows) != 'array' || count($rows) == 0) {
      return null;
    }
    return $rows[0];
  }

  static function updatePassword($token, $password, $email)
  {
    $repo = new Model("users");
    $salt = UUID::v4();
    $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['salt' => $salt]);
    $res = $repo
      ->Update()
      ->Where(['confirmation_token=' => $token])
      ->And(['email=' => $email])
      ->Set(['password' => $passwordHash, 'salt' => $salt, 'confirmation_token' => ''])
      ->Do();
    return !!$res;
  }

  static function generateGetBackToken($email)
  {
    if (!User::exists("", $email)) {
      return false;
    }
    $repo = new Model("users");
    $token = UUID::v4();
    $res = $repo
      ->Update()
      ->Where(['email=' => $email])
      ->Set(['confirmation_token' => $token])
      ->Do();
    if (!$res) return false;
    return $token;
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
    $salt = UUID::v4();
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
}
