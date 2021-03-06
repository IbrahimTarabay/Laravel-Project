<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Multipic;
use Illuminate\Support\Carbon;
use Image;

class BrandController extends Controller{

  public function AllBrand(){
    $brands = Brand::latest()->paginate(5);
    return view('admin.brand.index', compact('brands'));
  }

  public function StoreBrand(Request $request){
    $validatedData = $request->validate([
        'brand_name'=>'required|unique:brands|min:4',
        'brand_image'=>'required|mimes:jpg,jpeg,png',
      ],
      [
        'brand_name.required'=>'Please Input Brand Name',
        'brand_image.min'=>'Brand should be more than 4 chars',
      ]);

      $brand_image = $request->file('brand_image');

      $name_gen = hexdec(uniqid());
      $img_ext = strtolower($brand_image->getClientOriginalExtension());
      $img_name = $name_gen.'.'.$img_ext;
      $up_location = 'image/brand/';
      $last_img = $up_location.$img_name;
      //$brand_image->move($up_location,$img_name);

      //using intervention package
      Image::make($brand_image)->resize(300,200)->save($last_img);

      Brand::insert([
        'brand_name'=>$request->brand_name,
        'brand_image'=>$last_img,
        'created_at'=>Carbon::now()
      ]);

      return Redirect()->back()->with('success','Brand Inserted Successfully');
  }

  public function Edit($id){
    $brand = Brand::find($id);
    return view('admin.brand.edit',compact('brand'));
  }

  public function Update(Request $request, $id){

    $validatedData = $request->validate([
        'brand_name'=>'required|min:4',
      ],
      [
        'brand_name.required'=>'Please Input Brand Name',
        'brand_image.min'=>'Brand should be more than 4 chars',
      ]);

      $old_image = $request->old_image;
      $brand_image = $request->file('brand_image');

      if($brand_image){
        $name_gen = hexdec(uniqid());
        $img_ext = strtolower($brand_image->getClientOriginalExtension());
        $img_name = $name_gen.'.'.$img_ext;
        $up_location = 'image/brand/';
        $last_img = $up_location.$img_name;
        $brand_image->move($up_location,$img_name);
        unlink($old_image);
      }else{
        $last_img = $old_image;
      }

      Brand::find($id)->update([
        'brand_name'=>$request->brand_name,
        'brand_image'=>$last_img,
        'created_at'=>Carbon::now()
      ]);

      return Redirect()->back()->with('success','Brand Updated Successfully');

  }

  public function Delete($id){
    $image = Brand::find($id);
    $old_image = $image->brand_image;
    unlink($old_image);//to delete page from public folder

    Brand::find($id)->delete();
    return Redirect()->back()->with('success','Brand Delete Successfully');
  }

  //Multi Image methods

  public function Multpic(){
    $images = Multipic::all();
    return view('admin.multipic.index',compact('images'));
  }

  public function StoreImg(Request $request){

   $multi_img = $request->file('multi_img');

   foreach($multi_img as $img){
      $name_gen = hexdec(uniqid());
      $img_ext = strtolower($img->getClientOriginalExtension());
      $img_name = $name_gen.'.'.$img_ext;
      $up_location = 'image/multi/';
      $last_img = $up_location.$img_name;
      //$brand_image->move($up_location,$img_name);

      //using intervention package to save in public folder
      Image::make($img)->resize(300,300)->save($last_img);

      Multipic::insert([
        'image'=>$last_img,
        'created_at'=>Carbon::now()
      ]);
  }

    return Redirect()->back()->with('success','Multi Images Inserted Successfully');
  }
}
