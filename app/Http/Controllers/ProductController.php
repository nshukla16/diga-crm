<?php

namespace App\Http\Controllers;

use App\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $sort = $request->input('sort', 'created_at');
        if ($sort == '') {
            $sort = 'created_at';
        }
        $order = $request->input('order', 'asc');
        if ($order == '') {
            $order = 'asc';
        }

        $res = (object)array();
        $res->errcode = 0;
        try {
            $products = Product::with(['resource', 'estimate_unit', 'vat_type']);

            $category = $request->input('category', '');
            if ($category != '') {
                $products->where('category', $category);
            }

            $query = $request->input('query', '');
            if ($query != '') {
                $products->where('name', 'like', '%' . $query . '%')->orWhere('code', 'like', '%' . $query . '%');
            }

            $products->orderBy($sort, $order);

            $res->total = $products->count();
            $res->rows = $products->offset($offset)->limit($limit)->get();
        } catch (\Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $product = new Product();
            $product->fill($request->all());
            $product->save();
            $res->id = $product->id;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        return Product::with(['resource', 'estimate_unit', 'vat_type'])->find($id);
    }


    public function update(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $product = Product::find($id);
            $product->fill($request->all());
            $product->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        Product::find($id)->delete();
    }
}
