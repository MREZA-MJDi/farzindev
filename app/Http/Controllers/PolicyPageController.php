<?php
namespace App\Http\Controllers;
use App\Services\StorefrontPageContent;
use Illuminate\View\View;
class PolicyPageController extends Controller
{
 public function shipping(StorefrontPageContent $content): View { return $this->render($content,'shipping-returns','page_shipping','shipping-returns'); }
 public function warranty(StorefrontPageContent $content): View { return $this->render($content,'warranty','page_warranty','warranty'); }
 public function installments(StorefrontPageContent $content): View { return $this->render($content,'payment-installments','page_installments','payment-installments'); }
 public function faq(StorefrontPageContent $content): View { return $this->render($content,'faq','page_faq','faq'); }

 private function render(StorefrontPageContent $content,string $slug,string $prefix,string $routeName): View
 {
  $values=$content->all();
  $title=(string)($values[$prefix.'_title']??'اطلاعات خرید');
  $lead=(string)($values[$prefix.'_lead']??'');
  $sections=[];
  $faqs=[];
  if($slug==='faq'){
   for($i=1;$i<=6;$i++){
    $question=trim((string)($values[$prefix.'_'.$i.'_question']??''));
    $answer=trim((string)($values[$prefix.'_'.$i.'_answer']??''));
    if($question!==''&&$answer!=='') $faqs[]=['question'=>$question,'answer'=>$answer];
   }
  } else {
   for($i=1;$i<=3;$i++){
    $sectionTitle=trim((string)($values[$prefix.'_section_'.$i.'_title']??''));
    $body=trim((string)($values[$prefix.'_section_'.$i.'_body']??''));
    if($sectionTitle!==''&&$body!=='') $sections[]=['title'=>$sectionTitle,'body'=>$body];
   }
  }
  return view('pages.policy',[
   'pageSlug'=>$slug,'pageTitle'=>$title,'pageLead'=>$lead,'sections'=>$sections,'faqs'=>$faqs,
   'canonicalRoute'=>$routeName,
  ]);
 }
}
