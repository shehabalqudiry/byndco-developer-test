<?php
namespace Tests\Unit;

use App\Http\Controllers\TestController;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FizzBuzzTest extends TestCase
{
    public function testFizzBuzzDefault()
    {
        $expected = '12Fizz4BuzzFizz78FizzBuzz11Fizz1314FizzBuzz1617Fizz19BuzzFizz2223FizzBuzz26Fizz2829FizzBuzz3132Fizz34BuzzFizz3738FizzBuzz41Fizz4344FizzBuzz4647Fizz49BuzzFizz5253FizzBuzz56Fizz5859FizzBuzz6162Fizz64BuzzFizz6768FizzBuzz71Fizz7374FizzBuzz7677Fizz79BuzzFizz8283FizzBuzz86Fizz8889FizzBuzz9192Fizz94BuzzFizz9798FizzBuzz';

        $this->assertEquals($expected, (new TestController())->fizzBuzz(1, 100));
    }

    public function testFizzBuzzCustom()
    {
        $expected = '11Fizz1314FizzBuzz1617Fizz19';
        $this->assertEquals($expected, (new TestController())->fizzBuzz(11, 19));
    }

    public function testFizzBuzzNegative()
    {
        $this->expectException(InvalidArgumentException::class);
        (new TestController())->fizzBuzz(-5, 5);
    }

    public function testFizzBuzzInvalidParams()
    {
        $this->expectException(InvalidArgumentException::class);
        (new TestController())->fizzBuzz(100, 1);
    }
}
