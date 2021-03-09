<?php

namespace App\Http\Controllers\API;

use App\Book;
use App\BookInfo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;
use Validator;

class bookInfoController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'image' => 'required',
                'image.*' => 'image|mimes:jpg,jpeg,png',
                'price' => 'integer|required',
                'BookID' => 'integer|required|exists:books,id',
                'Edition' => 'string',
                'Format' => 'string',
                'CustomerID' => 'integer|required|exists:customers,id',
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    "responseCode" => 401,
                    "errorCode" => 'incomplete data',
                    'message' => $validator->errors(),
                ], 401);
        }

        $file_name_array = [];

        foreach($request->file('image') as $key => $file){

            if ($file != null) {
                $ext = $file->extension();
                $file_name = time() . mt_rand() . "." . $ext;
                $file->move(public_path(
                    'images/books/'
                ), $file_name);
                $color = Color::fromIntToHex((new ColorExtractor(
                    Palette::fromFilename(
                        public_path('images/books/') . $file_name
                    )))->extract(1)[0]);
                $file_name_array[] = $file_name;
            }
        }

        BookInfo::query()->Create(
            [
                'BookID' => $request->BookID,
                'Price' => $request->price,
                'Edition' => $request->Edition,
                'CustomerID' => $request->CustomerID,
                'format' => $request->Format,
                'Images' => implode("," , $file_name_array),
            ]
        );

        return response()->json(["success" => ["message" => "new book info created!"]], 200);
    }

    /**
     * Display the specified resource.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (!is_numeric($id))
            $id = Book::query()->Where("BookID", $id)->get("id")[0]["id"];

        $book_info = BookInfo::query()->Where("BookID", $id)->get();

        if (count($book_info) == 0) {
            return response()->json(["message" => "not found!"], 404);
        }

        foreach ($book_info as $item) {
            $item->translator;
            $item->customer;
        }

        return response()->json(["data" => $book_info], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
