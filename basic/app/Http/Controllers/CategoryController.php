<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;//to use query builder

class CategoryController extends Controller{
  public function AllCat(){

    /*$categories = DB::table('categories')
      ->join('users','categories.user_id','users.id')
      ->select('categories.*','users.name')
      ->latest()->paginate(5);*/

    $categories = Category::latest()->paginate(5);//eloquent
    //$categories = DB::table('categories')->latest()->paginate(5);//query builder

    $trashCat = Category::onlyTrashed()->latest()->paginate(3);
    return view('admin.category.index', compact('categories','trashCat'));
  }

  public function AddCat(Request $request){
    $validatedData = $request->validate([
      'category_name'=>'required|unique:categories|max:255',
    ],
    [
      'category_name.required'=>'Please Input Category Name',
      'category_name.max'=>'Category should be less than 255 chars',
    ]);

    //eloquent ORM
    Category::insert([
      'category_name'=>$request->category_name,
      'user_id'=>Auth::user()->id,
      'created_at'=>Carbon::now()
    ]);

    // another format of eloquent
    /*$category = new Category;
    $category->category_name = $request->category_name;
    $category->user_id = Auth::user()->id;
    $category->save();*/

    //Query Builder
    /*$data = array();
    $data['category_name'] = $request->category_name;
    $data['user_id'] = Auth::user()->id;
    DB::table('categories')->insert($data);*/

    return Redirect()->back()->with('success','Category Inserted Successfully');
  }

  public function Edit($id){
    //$category = Category::find($id);//eloquent ORM
    $category = DB::table('categories')->where('id',$id)->first();//query builder
    return view('admin.category.edit',compact('category'));
  }

  public function Update(Request $request, $id){
    //Eloquent ORM
    /*$update = Category::find($id)->update([
      'category_name'=>$request->category_name,
      'user_id'=>Auth::user()->id
    ]);*/

    //query builder
    $data = array();
    $data['category_name'] = $request->category_name;
    $data['user_id'] = Auth::user()->id;
    DB::table('categories')->where('id',$id)->update($data);

    return Redirect()->route('all.category')->with('success','Category Updated Successfully');
  }

  public function SoftDelete($id){
    $delete = Category::find($id)->delete();
    return Redirect()->back()->with('success','Category Soft Deleted Successfully');
  }

  public function Restore($id){
    $delete = Category::withTrashed()->find($id)->restore();
    return Redirect()->back()->with('success','Category Restored Successfully');
  }

  public function Pdelete($id){
    $delete = Category::onlyTrashed()->find($id)->forceDelete();
    return Redirect()->back()->with('success','Category Permanently Deleted');
  }
}