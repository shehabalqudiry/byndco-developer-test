<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use InvalidArgumentException;

class TestController extends Controller
{
    public function q1()
    {
        $q = "
            Question 1 <br>
                A client has called and said that they're noticing performance problems on their database when<br>
                searching for a user by email address. You've checked, and the following query is running:<br>
                SELECT * FROM users WHERE email = 'user@test.com';<br>
                You run the EXPLAIN command and get the following results:<br>
                +----+-------------+-------+------+---------------+------+---------+------+-------+-------------+<br>
                | id | select_type | table | type | possible_keys | key | key_len | ref | rows | Extra |
                <br>+----+-------------+-------+------+---------------+------+---------+------+-------+-------------+<br>
                | 1 | SIMPLE | users | ALL | NULL | NULL | NULL | NULL | 10320 | Using where |<br>
                +----+-------------+-------+------+---------------+------+---------+------+-------+-------------+<br>
                <br>Offer a theory as to why the performance is slow.
        ";
        // This answer for Question 1 I think there are other better solutions that can be tried, and I hope to reach them in the near future
        $res = "
            <br>
            <br>
            <br>
            <br>
            result <br>

            Based on the EXPLAIN output, it appears that the query is performing a full table scan (type=ALL and possible_keys=NULL) on the users table,<br >
            which has 10,320 rows (rows=10320). This means that the database is checking every row in the table to find any that match the email address provided in the query.
            <br>
            <br>
            Performing a full table scan can be slow and resource-intensive, especially on large tables with many rows. To improve performance, a few options could be considered:
            <br>

            1 - Add an index on the email column in the users table
            <br>
            <br>
            <code>ALTER TABLE users ADD INDEX email_index_name (email);</code>
            <br>
            <br>
            This would allow the database to use the index to quickly locate the rows that match the email address instead of having to scan the entire table.
            <br>
            <br>
            2 - Modify the query to use a more selective WHERE clause that narrows down the number of rows that need to be checked. For example, if there are certain criteria such as a created_at, updated_at, added_By, last_login or any other that can be used to filter the results, adding that to the WHERE clause could help reduce the number of rows that need to be checked.
            <br>
            <br>

            3 - Subsequent requests to the same email address can be served from the cache instead of having to query the database each time.
            <br>
        ";

        return $q . $res;
    }

    public function q2($request = "No")
    {
        $arr = [
            1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20
        ];
        // This for return array for you to show
        $arr_st = "array = [
            1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20
        ]";

        // I use in_array() native php function in my code for search in conditions
        $res = in_array($request, $arr) ? "true" : "false";
        return $arr_st . " <br><br> Your request : $request <br><br> Result : " . $res;
    }

    public function addToCart(CreateCartRequest $request)
    {
        $vendor = resolve('vendor');

        $user = $request->user('sanctum');
        $branch_id = $request->header('x-branch-id', null);

        if (empty($branch_id)) {
            return $this->returnError('400', 'No Branch Found Please Add Branch Or Area');
        }

        $sessionId = isset($user) ? $user->id : $request->UserAgent;
        if (!$sessionId) {
            return $this->returnError('400', "Add valid token");
        }

        if (empty($request->Cart)) {
            $this->clearCart($sessionId);
            return $this->returnSuccessMessage('Item Deleted From Cart Success');
        }

        $branch = $this->checkBranchForCart($branch_id, $request->Cart);
        if ($branch) {
            return $this->returnError('400', 'No Branch Found In This Area');
        }

        $outOfStockItems = $this->cart_has_out_of_stock_items($request->Cart)['result'];
        if ($outOfStockItems) {
            return $this->returnError('400', "Quantity of $outOfStockItems Out Of Stock ");
        }

        $this->clearCartIfNeeded($sessionId);

        foreach ($request->Cart as $shopping) {
            $this->cartItem->handle($shopping, $sessionId, $vendor->id);
        }

        $cart = Cart::session($sessionId);
        if ($cart->getContent()->isNotEmpty()) {
            $cart_summery = $this->calculateCartSummary($cart);
            $user_cart = array_values($cart->getContent()->toArray());
            $freeDelivery = $cart_summery['subTotal'] >= $vendor->storeSetting->free_delivery;
            if ($vendor->storeSetting->free_delivery && $freeDelivery) {
                $cart_summery = $this->calculateCartSummary($cart, null, true);
            }

            $data = [
                'Cart' => \App\Http\Resources\Api\Cart\CartResource::collection($user_cart),
                'minimum_order_price' => getDeliveryZone() ? getDeliveryZone()->minimum_order_price : "",
                "free_delivery_data" => $vendor->storeSetting ? $vendor->storeSetting->free_delivery : "",
                'subTotal' => $cart_summery['subTotal'],
                'total' => $cart_summery['total'],
                'delivery_fees' => $cart_summery['delivery_fees'],
                'tax' => getTax(),
                'discount' => $cart_summery['discount']
            ];

            return $this->returnData('data', $data, 'User Cart Data');
        } else {
            return $this->returnData('data', [], 'Cart is empty');
        }
    }

    private function clearCart($sessionId)
    {
        \Cart::session($sessionId)->clear();
    }

    private function clearCartIfNeeded($sessionId)
    {
        if (\Cart::session($sessionId)->isEmpty()) {
            $this->clearCart($sessionId);
        }
    }

    private function calculateCartSummary($cart, $coupon = null, $freeDelivery = false)
    {
        // be sure there is no old promo code that affects the calculation
        $cart->removeConditionsByType(config('promos'));

        if ($coupon) {
            $cart->condition($coupon);
        }

        if ($freeDelivery) {
            $cart->condition(new FreeDelivery());
        }

        return $this->cartItem->getCartSummery($cart);
    }



    public function fizzBuzz($start = 1, $stop = 100)
    {

        $string = '';
        if ($stop < $start || $start < 0 || $stop < 0) {
            throw new InvalidArgumentException();
        }
        for ($i = $start; $i <= $stop; $i++) {
            if ($i % 3 == 0 && $i % 5 == 0) {
                $string .= 'FizzBuzz';
                continue;
            }
            if ($i % 3 == 0) {
                $string .= 'Fizz';
                continue;
            }
            if ($i % 5 == 0) {
                $string .= 'Buzz';
                continue;
            }
            $string .= $i;
        }
        return $string;
    }
}
