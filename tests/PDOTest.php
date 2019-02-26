<?php

use vicF\PDO;

class PDOTest extends \PHPUnit\Framework\TestCase
{

    public function sqlReplaceArrayPlaceholdersProvider()
    {
        return [
            ['?', [1], '?'], // One param, no changes
            ['?---', [[1,1,1]], '?,?,?---'],
            ['select e from abc where u=? and y=?', ['ghjk', 344], 'select e from abc where u=? and y=?'],
            ['select e from abc where u in (?) and y=?', [[1, 2, 3], 344], 'select e from abc where u in (?,?,?) and y=?'],
            ['--?--?---?', [[1,2,3], [1], [1,2,3,4,5]], '--?,?,?--?---?,?,?,?,?']
        ];
    }

    /**
     * @param $sql
     * @param $args
     * @param $resultSql
     * @dataProvider sqlReplaceArrayPlaceholdersProvider
     */
    public function testReplaceArrayPlaceholders($sql, $args, $resultSql)
    {
        $pdo = new PDO('sqlite::memory:');
        $this->assertEquals($resultSql, $pdo->replaceArrayPlaceholders($sql, $args));
    }

    /**
     * @return array
     */
    public function providerFlattenArguments(): array
    {
        return [
            [[1,2], [1,2]],
            [[[1,2,3],2], [1,2,3,2]],
            [[[1,2,3],[1,2]], [1,2,3,1,2]],
        ];
    }

    /**
     * @param $input
     * @param $expected
     * @dataProvider providerFlattenArguments
     */
    public function testFlattenArguments($input, $expected)
    {
        $pdo = new PDO('sqlite::memory:');
        $this->assertEquals($expected, $pdo->flattenArguments($input));

    }
}