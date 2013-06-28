1.9
===================

  1. 윈도우 OS 환경 지원
  2. 최신글 리스트 URL 버그 수정



1.8
===================

  1. avatar 스킨 추가 - 작성자 이름 대신 아바타 이미지 표시
  2. customer 스킨 추가 - 이름, 연락처 입력 가능
  3. 최신글 리스트 생성 기능 추가
  4. 작성자 검색 기능 추가
  5. 버그 수정



1.7
===================

  1. 기본스킨에 제목, 비밀번호 등 Validation 스크립트 추가로 저장 전에 폼 데이터 확인
  2. 공지사항은 권한 상관없이 누구나 글 읽기 가능
  3. KBoard 댓글 플러그인 필수 설치 경고창 추가
  4. 기본스킨에 향상된 반응형 웹(Responsive web) 적용
  5. 버그 수정



1.6
===================

  1. 실제 경로와 서버의 DOCUMENT ROOT 값이 다를 때에도 정상적으로 동작 가능
  2. KBoardSkin 클래스 싱글톤 패턴(Singleton Pattern) 적용
  3. 기본 스킨의 리스트 페이지에서 썸네일 삭제하고 썸네일 스킨 추가
  4. 보안 : iframe 주소 화이트리스트 기능 추가
  5. 버그 수정



1.5
===================

  1. BoardBuilder() 클래스 버그 수정
  2. ContentList() 클래스 버그 수정
  3. 메소드 추가 : ContentList::init()
  4. 메소드 추가 : ContentList::initWithRSS()
  5. 통합 RSS 피드 기능 추가 : 게시물 피드 공개. 구글, 야후, Bing 등 검색엔진에 Sitemaps 제출이 가능.
  6. 게시판 스킨 document.php 기능 추가 : schema.org를 이용한 콘텐츠 마크업을 적용. SEO 대폭 상승 (참고:schema.org)
  7. 게시판 스킨 list.php 기능 추가 : powered by



1.4
===================

  1. 파일업로드 확장자 체크 버그 수정
  2. 파일명 변경 : Pagination.function.php -> Pagination.helper.php
  3. 파일 추가 : Security.helper.php
  4. 보안 강화



1.3
===================

  1. 같이 압축되어 있던 kboard, kboard-comments 플러그인들을 각각 압축해서 배포함
  2. 클래스 추가 : Comment()
  3. 클래스명 변경 : Comments() -> CommentList()
  4. 클래스명 변경 : Skin() -> KBoardSkin()
  5. 클래스명 변경 : Board() -> KBoard()
  6. 메소드 추가 : ContentList::hasNextNotice()
  7. 메소드 삭제 예정 : ContentList::hasNoticeNext()
  8. 게시판 고유주소 기능 강화
  9. 비회원 댓글 작성 가능
  10. 관리자 페이지에서 전체 댓글 리스트 관리
  11. 게시판 및 댓글 기본스킨 CSS 업데이트
  12. kboard_pagination 함수 및 페이징 CSS 업데이트



1.2
===================

  1. 자잘한 버그 수정



1.1
===================

  1. 워드프레스가 루트디렉토리가 아닌 서브디렉토리에 있을때 게시판 생성 가능
  2. 글 수정시 읽은숫자 초기화 문제 수정
