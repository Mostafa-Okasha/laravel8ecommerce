<?php

namespace App\Http\Livewire;

use App\Models\Product;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Cart;

class ShopComponent extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    protected $listeners = ['refreshComponent'=>'$refresh'];

    public $sorting;
    public $pagesize;

    public $min_price;
    public $max_price;

    public function mount()
    {
        $this->sorting = "default";
        $this->pagesize = 12;

        $this->min_price = 1;
        $this->max_price = 1000;
    }

    public function store($product_id,$product_name,$product_price)
    {
        //add is a function take 4 parameter id, name,quantity and price
        Cart::instance('cart')->add($product_id,$product_name,1,$product_price)->associate('App\Models\product');
        session()->flash('success_message','Item added in Cart');
        return redirect()->route('product.cart');
    }
    // add to wish list function
    public function addToWishlist($product_id,$product_name,$product_price)
    {
        Cart::instance('wishlist')->add($product_id,$product_name,1,$product_price)->associate('App\Models\product');
        $this->emitTo('wish-list-count-component','refreshComponent');
        $this->emitTo('shop-component','refreshComponent');
    }
    // remove to wish list function
    public function removeFromWishlist($product_id)
    {
        foreach(Cart::instance('wishlist')->content() as $witem)
        {
            if($witem->id == $product_id)
            {
                Cart::instance('wishlist')->remove($witem->rowId);
                $this->emitTo('wish-list-count-component','refreshComponent');
            }
        }
    }
    public function render()
    {
        if($this->sorting=='date')
        {
            $products = Product::whereBetween('regular_price',[$this->min_price,$this->max_price])->orderBy('created_at','DESC')->paginate($this->pagesize);
        }
        elseif($this->sorting=='price')
        {
            $products = Product::whereBetween('regular_price',[$this->min_price,$this->max_price])->orderBy('regular_price','ASC')->paginate($this->pagesize);
        }
        elseif($this->sorting=='price-desc')
        {
            $products = Product::whereBetween('regular_price',[$this->min_price,$this->max_price])->orderBy('regular_price','DESC')->paginate($this->pagesize);
        }
        else
        {
            $products = Product::whereBetween('regular_price',[$this->min_price,$this->max_price])->paginate($this->pagesize);
        }
        $categories=Category::all();
        return view('livewire.shop-component',['products'=>$products,'categories'=>$categories])->layout("layouts.base");
    }
}
