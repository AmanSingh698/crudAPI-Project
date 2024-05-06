<?php

namespace App\Http\Controllers;

use App\Models\Book;
// use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    protected $collection = 'books';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::all();
        return response()->json($books);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'author' => 'required|string',
            'genre' => 'required|string',
            'year' => 'required|integer',
            'owner' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();

        DB::collection($this->collection)->insert($data);
        return response()->json($data, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $books = DB::collection($this->collection)->find($id);

        if (!$books) {
            return response()->json(['message' => 'Blog post not found'], 404);
        }

        return response()->json($books);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'author' => 'required|string',
            'genre' => 'required|string',
            'year' => 'required|integer',
            'owner' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();

        $books = DB::collection($this->collection)->find($id);

        if (!$books) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        DB::collection($this->collection)
        ->where('_id', $id)->update(
            $request->all()
        );

    return response()->json(['message' => 'Book has been updated successfully'],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $notes = DB::collection($this->collection)->find($id);
        if (!$notes) {
            return response()->json(['message' => 'Books not found'], 404);
        }
        DB::collection($this->collection)->delete($id);
        return response()->json(['message' => 'Book has been deleted successfully'], 204);
    }

    public function lookupBooksAndCategories()
    {
        // Perform the aggregation pipeline
        $books = Book::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$lookup' => [
                        'from' => 'categories', // The collection to join with
                        'localField' => 'genre', // The field from the "books" collection
                        'foreignField' => 'name', // The field from the "categories" collection
                        'as' => 'category_info', // The name of the new field to store the joined documents
                    ],
                ],
                [
                    '$unwind' => '$category_info', // Deconstructs the array produced by the $lookup stage
                ],
                [
                    '$project' => [
                        '_id' => 1, // Include the original book fields
                        'title' => 1,
                        'author' => 1,
                        'year' => 1,
                        'category_name' => '$category_info.name', // Include the category name from the joined documents
                    ],
                ],
            ]);
        });

        return response()->json($books);

        //Lookup and aggregation stages on collection users and books

        // $books = Book::raw(function ($collection) {
        //     return $collection->aggregate([
        //         [
        //             '$lookup' => [
        //                 'from' => 'users', // The collection to join with
        //                 'localField' => 'owner', // The field from the "books" collection
        //                 'foreignField' => 'name', // The field from the "users" collection
        //                 'as' => 'user_info', // The name of the new field to store the joined documents
        //             ],
        //         ],
        //         [
        //             '$unwind' => '$user_info', // Deconstructs the array produced by the $lookup stage
        //         ],
        //         [
        //             '$project' => [
        //                 '_id' => 1, // Include the original book fields
        //                 'title' => 1,
        //                 'author' => 1,
        //                 'year' => 1,
        //                 'user_name' => '$user_info.name', // Include the user name from the joined documents
        //             ],
        //         ],
        //     ]);
        // });

        // return response()->json($books);
    }
}
