KBoard-wordpress-plugin
=======================

한국형 워드프레스 게시판 KBoard 입니다.
자세한 설명은 홈페이지를 참조해주세요.
http://www.cosmosfarm.com/products/kboard


꼭 게시판과 댓글을 함께 설치 해주세요.
--------------------------------------
KBoard 사용에 궁금하신 점은 http://www.cosmosfarm.com/threads 에 질문을 남겨주세요.



Examples (사용 예제)
--------------------

1. http://www.cosmosfarm.com/demo/kboard/
2. http://smart-k.com/?page_id=1692
3. http://www.bluehillclinic.com/?page_id=427
4. http://www.shoetoker.com/?page_id=1707
5. http://tourjoin.com/%EA%B2%AC%EC%A0%81%EB%AC%B8%EC%9D%98-2/
6. http://iphonebuyer.co.kr/?page_id=6650



Description (소개)
------------------

1. <워드프레스(WordPress) 플러그인>
KBoard는 세계에서 가장 많이 사용하는 CMS인 워드프레스의 플러그인으로 제공되고 동작합니다. 플러그인 파일을 업로드하고 활성화 시켜주는것 만드로 설치는 완료됩니다.

2. <파워풀한 기능>
게시판 레이아웃과 다양한 옵션으로 각각의 게시판마다 독립적인 운영정책을 가질 수 있습니다. 하지만 두려워 마세요, 왜냐하면 KBoard는 간단히 설치하고 즉시 게시판을 생성할 수 있기때문입니다.

3. <친숙한 사용자 경험>
KBoard는 워드프레스에 무수히 많은 기존의 포럼형 게시판과는 완전히 다릅니다. 공지사항과 커뮤니티를 운영할 수 있고 자료실과 포토갤러리도 충분히 가능합니다. 이제 워드프레스 홈페이지에서도 일반적인는 게시글과 댓글을 작성할 수 있습니다.

4. <다양한 스킨>
똑같은 웹사이트는 없기 때문에 웹사이트의 아이덴티티에 맞춰 새롭게 디자인 될 필요가 있습니다. KBoard는 다양한 스킨을 통해 완전 새로운 게시판으로 바뀔 수 있습니다. 하지만, 모던하며 아름다운 디자인의 기본 스킨만으로도 충분합니다.

5. <최신글 리스트 그리고 반응형 웹>
게시판을 적용했다면, 메인화면에 그리고 사이드바에 조그만 최신글 리스트도 설치해보세요. 웹사이트가 훨씬 스마트해 보이게 됩니다. 또한 반응형 웹(Responsive Web)이 적용된 스킨이라면 어떠한 스마트 디바이스에서도 문제없이 표시됩니다.

6. <광고 비용 절약>
KBoard는 검색엔진 최적화(SEO)가 적용된 스킨을 내장하고 있습니다. 또한 RSS피드를 거대 검색엔진의 사이트맵으로 등록하세요. KBoard에 내장된 기능들을 활용하면, 비용지출을 최소화 할 수 있습니다.



Installation (설치)
-------------------

1. Upload 'kboard','kboard-comments' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'kboard','kboard-comments' menu in WordPress



Expansion (확장)
-------------------
1. kboard_content 필터(Filter) 사용법
<pre><code>
add_filter('kboard_content', 'kboard_content_extend');
function kboard_content_extend($content){
 // kboard_content 필터는 게시글 본문 내용을 입력 받습니다.
 // 내용을 편집 및 추가할 수 있습니다.
 $content = $content . '<br>kboard_content_extend 실행';
 // 최종 내용을 반환합니다.
 return $content;
</code></pre>

2. kboard_document_insert 액션(Action)
3. kboard_document_update 액션(Action)
4. kboard_document_delete 액션(Action)
