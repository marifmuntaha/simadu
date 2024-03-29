<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Portal\Category;
use App\Models\Portal\Comment;
use App\Models\Portal\Event;
use App\Models\Portal\Extracurricular;
use App\Models\Portal\Facility;
use App\Models\Portal\Post;
use App\Models\Portal\Program;
use App\Models\Portal\Section;
use App\Models\Portal\Setting;
use App\Models\Portal\Slider;
use App\Models\Portal\Tag;
use App\Models\Portal\Teacher;
use App\Models\Portal\Testimonial;
use Illuminate\Http\Request;

class FrontedController extends Controller
{
    public $data;
    public $setting;

    public function __construct()
    {
        $this->setting = new Setting();
        $this->data['setting'] = $this->setting;
        $this->data['meta'] = (object) [
            'description'   => 'Portal Resmi MTs. Darul Hikmah Menganti',
            'keyword'       => 'Portal, portal resmi, madrasah, madrasah tsanawiyah, mts darul hikmah, mts darul hikmah menganti',
            'author'        => 'MTs. Darul Hikmah Menganti'
        ];
        $this->data['section'] = new Section();
        $this->data['title'] = $this->setting->value('app_name') .' - '. $this->setting->value('school_name');
    }

    public function home()
    {
        $this->data['page']     = 'BERANDA';
        $this->data['section']  = new Section();
        $this->data['sliders']  = Slider::where('slider_status', 1)->get();
        $this->data['programs'] = Program::all();
        $this->data['facilities'] = Facility::limit(4)->get();
        $this->data['events']   = Event::where('event_date_start', '>', now())->orderBy('event_date_start', 'ASC')->limit(3)->get();
        $this->data['extracurriculars'] = Extracurricular::limit(10)->get();
        $this->data['testimonials'] = Testimonial::all();
        $this->data['post_single'] = Post::first();
        $this->data['posts'] = Post::orderBy('created_at', 'DESC')->limit(3)->get();
        $this->data['teachers'] = Teacher::orderBy('teacher_name', 'ASC')->limit(4)->get();
        return view('portal.fronted.home', $this->data);
    }

    public function article()
    {
        $this->data['page']     = 'ARTIKEL';
        $this->data['section']  = new Section();
        $this->data['categories'] = Category::orderBy('category_name', 'ASC')->get();
        $this->data['posts'] = Post::orderBy('created_at', 'DESC')->paginate(4);
        $this->data['populars'] = Post::orderBy('post_read', 'DESC')->limit(4)->get();
        return view('portal.fronted.article', $this->data);
    }

    public function article_read($id, Request $request)
    {
        if ($request->isMethod('post')){
            $comment = new Comment();
            $comment->comment_name = $request->comment_name;
            $comment->comment_email = $request->comment_email;
            $comment->comment_content = $request->comment_content;
            $comment->comment_read = 1;
            try {
                $comment->save();
                $comment->post()->attach([1 => ['post_id' => $id]]);
                $msg = 'Komentar berhasil di simpan';
            }
            catch (\Exception $e) {
                $msg = $e->getMessage();
            }
            return redirect()->back()->withErrors($msg);
        }
        $this->data['page']     = 'ARTIKEL';
        $this->data['section']  = new Section();
        $this->data['categories'] = Category::orderBy('category_name', 'ASC')->get();
        $this->data['post'] = Post::where('post_id', $id)->first();
        $this->data['populars'] = Post::orderBy('post_read', 'DESC')->limit(4)->get();
        return view('portal.fronted.article_read', $this->data);
    }

    public function event()
    {
        $this->data['page'] = 'Acara & Kegiatan';
        $this->data['events'] = Event::orderBy('event_date_start', 'DESC')->paginate(8);
        return view('portal.fronted.event', $this->data);
    }

    public function event_read($id, Request $request)
    {
        $this->data['event'] = $event = Event::find($id);
        $this->data['page'] = $event->event_title;
        return view('portal.fronted.event_read', $this->data);
    }

    public function extracurricular()
    {
        $this->data['page']     = 'EKSTRAKURIKULER';
        $this->data['extracurriculars'] = Extracurricular::paginate(6);
        return view('portal.fronted.extracurricular', $this->data);
    }

    public function extracurricular_detail($id)
    {
        $this->data['page']     = 'EKSTRAKURIKULER';
        $this->data['extracurricular'] = Extracurricular::find($id);
        return view('portal.fronted.extracurricular_detail', $this->data);
    }

    public function teacher(){
        $this->data['page'] = 'PROFIL MADRASAH';
        $this->data['teachers'] = Teacher::orderBy('teacher_id', 'ASC')->paginate(8);
        return view('portal.fronted.teacher', $this->data);
    }

    public function teacher_detail($id)
    {
        $this->data['page'] = 'PROFIL MADRASAH';
        $this->data['teacher'] = Teacher::find($id);
        return view('portal.fronted.teacher_detail', $this->data);
    }

    public function category($id)
    {
        $this->data['page']     = 'ARTIKEL';
        $this->data['section']  = new Section();
        $this->data['categories'] = Category::orderBy('category_name', 'ASC')->get();
        $this->data['posts'] = Post::where('post_category', $id)->orderBy('created_at', 'DESC')->paginate(4);
        $this->data['populars'] = Post::orderBy('post_read', 'DESC')->limit(4)->get();
        return view('portal.fronted.article', $this->data);
    }

    public function facility()
    {
        $this->data['page'] = "FASILITAS";
        $this->data['facilities'] = Facility::paginate(6);
        return view('portal.fronted.facility', $this->data);
    }
}
