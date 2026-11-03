<?php

namespace app\commands;

use app\models\User;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Manages back-office accounts.
 *
 *   php yii user/create <username>     create an account (asks for the password)
 *   php yii user/password <username>   change a password
 *   php yii user/delete <username>     delete an account
 *   php yii user/list                  list accounts
 */
class UserController extends Controller
{
    public $defaultAction = 'list';

    public function actionCreate($username)
    {
        if (User::findByUsername($username)) {
            $this->stderr("User \"$username\" already exists. Use user/password to change the password.\n", Console::FG_RED);
            return ExitCode::DATAERR;
        }
        $user = new User(['username' => $username]);
        $user->setPassword($this->askPassword());
        if (!$user->save()) {
            $this->stderr(implode("\n", $user->getErrorSummary(true)) . "\n", Console::FG_RED);
            return ExitCode::DATAERR;
        }
        $this->stdout("User \"$username\" created. Sign in at /site/login\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    public function actionPassword($username)
    {
        $user = User::findByUsername($username);
        if (!$user) {
            $this->stderr("No user \"$username\".\n", Console::FG_RED);
            return ExitCode::DATAERR;
        }
        $user->setPassword($this->askPassword());
        $user->save(false);
        $this->stdout("Password changed for \"$username\". Their other sessions are signed out.\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    public function actionDelete($username)
    {
        $user = User::findByUsername($username);
        if (!$user) {
            $this->stderr("No user \"$username\".\n", Console::FG_RED);
            return ExitCode::DATAERR;
        }
        if (!$this->confirm("Delete user \"$username\"?")) {
            return ExitCode::OK;
        }
        $user->delete();
        $this->stdout("User \"$username\" deleted.\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    public function actionList()
    {
        $users = User::find()->orderBy('username')->all();
        if (!$users) {
            $this->stdout("No users yet. Create one: php yii user/create <username>\n");
        }
        foreach ($users as $u) {
            $this->stdout($u->username . '  (created ' . date('Y-m-d', $u->created_at) . ")\n");
        }
        return ExitCode::OK;
    }

    /** Asks for a password twice. Reads from TI_PASSWORD when not run interactively. */
    private function askPassword()
    {
        $env = getenv('TI_PASSWORD');
        if ($env !== false && $env !== '') {
            if (strlen($env) < User::MIN_PASSWORD) {
                throw new \yii\console\Exception('Password must be at least ' . User::MIN_PASSWORD . ' characters.');
            }
            return $env;
        }
        while (true) {
            $p1 = $this->hidden('Password (at least ' . User::MIN_PASSWORD . ' characters): ');
            if (strlen($p1) < User::MIN_PASSWORD) {
                $this->stderr("Too short.\n", Console::FG_RED);
                continue;
            }
            if ($p1 !== $this->hidden('Repeat password: ')) {
                $this->stderr("The passwords don't match.\n", Console::FG_RED);
                continue;
            }
            return $p1;
        }
    }

    private function hidden($label)
    {
        $this->stdout($label);
        $tty = DIRECTORY_SEPARATOR === '/' && function_exists('posix_isatty') && posix_isatty(STDIN);
        if ($tty) {
            shell_exec('stty -echo');
        }
        $value = rtrim((string)fgets(STDIN), "\r\n");
        if ($tty) {
            shell_exec('stty echo');
            $this->stdout("\n");
        }
        return $value;
    }
}
