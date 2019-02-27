<?php

namespace vicF\PDO;


/**
 * Class PDO
 *
 * @package vicF\PDO
 */
class PDO extends \PDO
{
    /**
     * Performs sql request. Prepares, binds an executes. So you can $pdo->run($sql, $arg1, $arg2)->fetchAll();
     *
     * @param $sql
     * @param mixed ...$args
     * @return \PDOStatement
     */
    public function run($sql, ...$args): \PDOStatement
    {
        return $this->runArrayArgs($sql, $args);
    }

    /**
     * Same as run, but accepts array of arguments
     *
     * @param $sql
     * @param $args
     * @return bool|\PDOStatement
     */
    public function runArrayArgs($sql, $args) {
        $st = $this->prepare($sql);
        if (!$st->execute($args)) {
            $arr = $st->errorInfo();
            throw new \PDOException('Failed to bind parameters: ' . $arr[0] . ' ' . $arr[2], $arr[1]);
        }
        return $st;
    }

    /**
     * Creates number of ? placeholders for each member of $args that is array
     *
     * @param $sql
     * @param mixed ...$args
     * @return string
     */
    public function replaceArrayPlaceholders($sql, $args):string
    {
        $num = 0;
        preg_match_all('/\?/', $sql, $matches, PREG_OFFSET_CAPTURE);  // Captures positions of placeholders
        //echo $matches[0][1][1];
        $replacements = [];
        foreach($args as $arg) {
            if(is_array($arg)) {
                $replacements[$matches[0][$num][1]] = implode(',',array_fill(0, count($arg), '?')); // Create placeholders string
            }
            $num++;
        }
        krsort($replacements);
        foreach($replacements as $position => $placeholders) {
            $sql = substr($sql, 0, $position).$placeholders.substr($sql, $position+1); // Replace single placeholder with multiple
        }
        return $sql;
    }

    /**
     * Turns [[1,2], 3] into [1,2,3]
     *
     * @param $args
     * @return array
     */
    public function flattenArguments($args): array
    {
        $res = [];
        foreach($args as $value) {
            if(is_array($value)) {
                foreach($value as $innerValue) {
                    $res[] = $innerValue;
                }
            } else {
                $res[] = $value;
            }
        }
        return $res;
    }

    /**
     * Treats each argument that is array as a list for IN(...). Creates appropriate number of "?" placeholders and binds arguments
     *
     * @param $sql
     * @param mixed ...$args
     * @return bool|\PDOStatement
     */
    public function runWithArrays($sql, ...$args) {
        return $this->runArrayArgs($this->replaceArrayPlaceholders($sql, $args),  $this->flattenArguments($args));
    }
}