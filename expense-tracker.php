#!/usr/bin/php
<?php

enum ErrMsg: string
{
  case MissingCmd = "Missing command: add, update, delete, list or summary\n";
  case InvalidCmd = "Invalid command: add, update, delete, list or summary\n";
  case MissingOpt = "Missing option\n";
  case MissingDesc = "Missing description option: --description or -d\n";
  case MissingAmount = "Missing amount option: --amount or -a\n";
  case MissingArg = "Missing argument\n";
  case EmptyArgument = "Empty argument\n";
  case InvalidArgNumber = "Invalid argument, must be a number\n";
  case MissingMonthOpt = "Missing month option: --month or -m\n";
}

$argi = 1;
function getArg(ErrMsg $msg = ErrMsg::MissingArg)
{
  global $argi;
  global $argv;
  if (!isset($argv[$argi]))
    exit($msg->value);
  return $argv[$argi++];
}
class JsonDatabase
{
  private const PATH = 'data/database.json';
  private array $data;
  function __construct()
  {
    if (!file_exists(self::PATH) || !filesize(self::PATH)) {
      $handle = fopen(self::PATH, 'w');
      fwrite($handle, '[]');
      fclose($handle);
      $this->data = array();
    } else {
      $this->data = json_decode(file_get_contents(self::PATH), true);
    }
  }
  private function write()
  {
    $handle = fopen(self::PATH, 'w');
    fwrite($handle, json_encode($this->data));
    fclose($handle);
  }
  public function add(string $description, int $amount): int
  {
    if ($this->data === array())
      $id = 1;
    else
      $id = $this->data[array_key_last($this->data)]['id'] + 1;
    $this->data[] = ['id' => $id, 'date' => date('Y-m-d', time()), 'description' => $description, 'amount' => $amount];
    $this->write();
    return $id;
  }
  public function update(int $id, ?string $description, ?string $amount)
  {
    $key = array_find_key($this->data, function (array $expense) use ($id) {
      return $expense['id'] === $id;
    });
    if (is_null($key)) {
      return;
    }
    if ($description) {
      $this->data[$key]['description'] = $description;
    }
    if ($amount) {
      $this->data[$key]['amount'] = $amount;
    }
    $this->write();
  }
  public function delete(int $id)
  {
    $key = array_find_key($this->data, function (array $expense) use ($id) {
      return $expense['id'] === $id;
    });
    if (is_null($key)) {
      return;
    }
    unset($this->data[$key]);
    $this->write();
  }
  public function list(): array
  {
    return $this->data;
  }
}
$command = getArg(ErrMsg::MissingCmd);
$jsondb = new JsonDatabase();
switch ($command) {
  case 'add':
    $descOpt = getArg(ErrMsg::MissingDesc);
    if ($descOpt !== '--description' && $descOpt !== '-d')
      exit(ErrMsg::MissingDesc->value);
    $desc = getArg(ErrMsg::MissingArg);
    if ($desc === "")
      exit(ErrMsg::EmptyArgument->value);
    $amountOpt = getArg(ErrMsg::MissingAmount);
    if ($amountOpt !== '--amount' && $amountOpt !== '-a')
      exit(ErrMsg::MissingAmount->value);
    $amount = getArg(ErrMsg::EmptyArgument);
    if (!is_numeric($amount))
      exit(ErrMsg::InvalidArgNumber->value);
    $id = $jsondb->add($desc, $amount);
    echo "Expense added successfully (ID: $id)\n";
    break;
  case 'update':
    $opt = getArg(ErrMsg::MissingArg);
    if (!is_numeric($opt)) {
      exit(ErrMsg::InvalidArgNumber->value);
    }
    $id = $opt;
    $opt = getArg(ErrMsg::MissingArg);
    if ($opt === '--description' || $opt === '-d') {
      $value = getArg(ErrMsg::MissingArg);
      if (!$value) {
        exit(ErrMsg::EmptyArgument->value);
      }
      $description = $value;
    } else if ($opt === '--amount' || $opt === '-d') {
      $value = getArg(ErrMsg::MissingArg);
      if (!is_numeric($value)) {
        exit(ErrMsg::InvalidArgNumber->value);
      }
      $amount = $value;
    }
    if (isset($argv[$argi])) {
      if ($opt === '--description' || $opt === '-d') {
        $value = getArg(ErrMsg::MissingArg);
        if (!$value) {
          exit(ErrMsg::EmptyArgument->value);
        }
        $description = $value;
      } else if ($opt === '--amount' || $opt === '-a') {
        $value = getArg(ErrMsg::MissingArg);
        if (!is_numeric($value)) {
          exit(ErrMsg::InvalidArgNumber->value);
        }
        $amount = $value;
      }
    }
    if (!isset($description))
      $description = null;
    if (!isset($amount))
      $amount = null;
    $jsondb->update($id, $description, $amount);
    break;
  case 'delete':
    $opt = getArg(ErrMsg::MissingArg);
    if (!is_numeric($opt)) {
      exit(ErrMsg::InvalidArgNumber->value);
    }
    $id = $opt;
    $jsondb->delete($id);
    break;
  case 'list':
    $expenses = $jsondb->list();
    $max = 1;
    foreach ($expenses as $expense) {
      $c = strlen($expense['description']);
      if ($max < $c)
        $max = $c;
    }
    printf("%-3s %-10s %-{$max}s %s\n", 'ID', 'Date', 'Description', 'Amount');
    foreach ($expenses as $expense) {
      ['id' => $id, 'date' => $date, 'description' => $desc, 'amount' => $amount] = $expense;
      printf("%-3s %-10s %-{$max}s \$%s\n", $id, $date, $desc, $amount);
    }
    break;
  case 'summary':
    $expenses = array_filter($jsondb->list(), function ($expense) {
      return substr($expense['date'], 0, 4) === date('Y', time());
    });
    if (!isset($argv[$argi])) {
      $total = array_sum(array_map(function ($expense) {
        return $expense['amount'];
      }, $expenses));
      echo "Total expenses: \$$total\n";
    } else {
      $opt = getArg();
      if ($opt !== '--month' && $opt !== '-m') {
        exit(ErrMsg::MissingMonthOpt->value);
      }
      $month = getArg(ErrMsg::MissingArg);
      if (!is_numeric($month) || (int)$month < 1 || (int)$month > 12) {
        exit(ErrMsg::InvalidArgNumber->value);
      }
      $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
      $total = 0;
      foreach ($expenses as $expense) {
        $currentMonth = explode('-', $expense['date'])[1];
        if ($month === $currentMonth) {
          $total += $expense['amount'];
        }
      }
      echo "Total expenses of {$months[(int)$month - 1]}: \$$total\n";
    }
    break;
  default:
    exit(ErrMsg::InvalidCmd->value);
}
