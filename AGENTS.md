# KBoard 스킨 복제 및 커스터마이징 가이드

이 문서는 기존 KBoard 스킨(예: `default`)을 기반으로 새로운 스킨(예: `[new-skin]`)을 생성하거나, 스타일을 이식하는 표준 절차를 기술합니다.

## ⚠️ 핵심 원칙 (Critical Rules)

1.  **UI/스타일만 변경한다**: CSS 클래스 교체, HTML 구조 변경(감싸는 div 등)은 가능하지만, **PHP 로직(Loop 구조, 변수명, `if` 조건 등)은 절대 변경하지 않는다.**
2.  **요청하지 않은 것은 건드리지 않는다**: 사용자가 명시적으로 수정을 요청한 부분(예: 아이콘 교체) 외에는 **기존 템플릿의 데이터 출력 형식(예: 날짜 포맷, 작성자 표시 방식, 줄바꿈 여부)을 그대로 보존**해야 한다.
3.  **⭐⭐⭐ 철저한 격리(Scoping) 필수 ⭐⭐⭐**: **CSS 스타일 충돌 방지는 선택이 아닌 필수입니다.** 모든 CSS 선택자와 HTML ID/Class는 해당 스킨 고유의 프리픽스로 반드시 변경해야 합니다. 만약 변경하지 않으면 다른 스킨을 사용하는 게시판과 스타일이 충돌하여 사이트 전체 디자인이 깨질 수 있습니다.

---

## 1. 개요

- **Source Skin**: `default` (모던 디자인 레퍼런스)
- **Target Skin**: `[new-skin]` (작업 대상 스킨명. 예: `avatar`, `thumbnail`, `question`)

## 2. 스타일 시트(CSS) 이식 및 격리 (가장 중요)

**목표**: `default` 스킨의 세련된 디자인을 가져오되, Target 스킨의 고유 프리픽스로 변경하여 충돌을 방지함.

### 2-1. 구조 및 순서 유지 (Maintain Structure & Order)
유지보수와 비교(Diff)를 용이하게 하기 위해, **CSS 파일의 코드 순서와 구조는 원본(`default`)과 100% 동일하게 유지**해야 합니다.

*   ❌ **Don't**: 코드 순서를 임의로 뒤섞거나 재정렬하지 마십시오.
*   ✅ **Do**: `default/style.css`를 그대로 복사한 뒤, 프리픽스만 치환하여 사용하십시오.
*   ✅ **Do**: 스킨 고유의 스타일이 필요하다면, 기존 코드 중간에 끼워넣지 말고 **파일 최하단에 별도 섹션(Custom Styles)**을 만들어 추가하십시오.

### 🛑 실수하기 쉬운 포인트 (주의!)
단순히 파일만 복사하고 ID/Class를 변경하지 않으면, `default` 스킨의 스타일이 덮어씌워지거나 오작동합니다. **반드시 아래 치환 작업을 선행해야 합니다.**

### 단계
1. `default/style.css` 내용 전체를 `[new-skin]/style.css`로 복사.
2. **Find & Replace (일괄 치환) - 꼼꼼하게 진행**:
   - `#kboard-default-` → `#kboard-[new-skin]-`
   - `.kboard-default-` → `.kboard-[new-skin]-`
   
   *(예시: `avatar` 스킨이라면 `#kboard-avatar-list`, `.kboard-avatar-button` 등으로 변경)*

3. **고유 스타일 추가 (파일 최하단)**:
   - `[new-skin]` 스킨만의 특징(예: 둥근 프로필 이미지)은 파일 맨 아래에 주석과 함께 추가합니다.
   ```css
   /* =========================================================
      [new-skin] Unique Styles (Custom Added)
      ========================================================= */
   .kboard-[new-skin]-list .kboard-list-user img { ... }
   ```

## 3. 아이콘 시스템 (Icon System)

최신 KBoard 스킨은 이미지 파일(`img` 태그) 대신 CSS Vector 아이콘(`span` 태그 + CSS Mask)을 사용하는 추세입니다.

### 3-1. 주요 아이콘 목록 및 권장 방식

| 아이콘 역할 | 기존 방식 (Deprecated) | 권장 방식 (Recommended) | CSS 클래스명 |
| :--- | :--- | :--- | :--- |
| **비밀글** | `<img src=".../icon-lock.png">` | `<span class="kboard-icon-lock"></span>` | `.kboard-icon-lock` |
| **답글** | `<img src=".../icon-reply.png">` | `<span class="kboard-icon-reply"></span>` | `.kboard-icon-reply` |
| **알림(New)** | `<span class="new">New</span>` | (동일, 스타일 변경) | `.kboard-[new-skin]-new-notify` |
| **첨부파일** | (텍스트 링크) | (업로드시 자동 생성) | `.kboard-button-download` 등 |

### 3-2. 아이콘 변경 시 수정해야 할 파일 목록

아이콘은 여러 뷰 템플릿에 흩어져 있으므로 아래 파일들을 모두 확인하여 일괄 수정해야 합니다.

1.  **`style.css`** (필수)
    *   아이콘 클래스(`kboard-icon-lock`, `kboard-icon-reply`) 정의 및 SVG Mask 스타일 추가.
2.  **`list.php`** (필수)
    *   게시판 목록 화면. 제목 옆 비밀글 아이콘, 공지사항 아이콘 등.
3.  **`latest.php`** (필수)
    *   최신글 위젯 화면. 여기에도 비밀글 아이콘이 사용됨.
4.  **`reply-template.php`** (스킨에 따라 존재)
    *   답글 계층 구조를 보여줄 때 사용. **답글 화살표(`icon-reply.png`)**가 여기에 위치함.

> **Tip**: 해당 스킨 디렉토리에서 `.png` 또는 `icon-`으로 문자열 검색(grep)을 수행하여 놓친 부분을 찾으십시오.

### 3-3. CSS 아이콘 스타일 예시 (SVG Mask)
```css
.kboard-icon-lock {
    display: inline-block;
    width: 14px;
    height: 14px;
    background-color: var(--kboard-text-muted);
    -webkit-mask: url("data:image/svg+xml;...") no-repeat center/contain;
    mask: url("data:image/svg+xml;...") no-repeat center/contain;
}
```

## 4. PHP 템플릿 파일 수정 가이드

스타일이 변경되었으므로 HTML 구조와 클래스명을 맞춰야 합니다. 하지만 **데이터 로직은 절대 건드리지 마십시오.**

### ✅ 변경해야 할 것 (DO)
1. **Container ID**: 
   - Before: `<div id="kboard-default-list">` 
   - After: `<div id="kboard-[new-skin]-list">`
2. **Class Names**: css 파일에서 변경한 프리픽스에 맞춰 수정.
   - Before: `class="kboard-default-button-small"` 
   - After: `class="kboard-[new-skin]-button-small"`
3. **Icons**: `<img>` 태그를 `<span class="kboard-icon-..."></span>`으로 교체 (단, 사용자가 요청한 경우에만).

### ❌ 변경하면 안 되는 것 (DON'T)
1. **PHP Logic**: `while`, `if`, `foreach` 등 제어문 구조.
2. **Data Format**: 
   - 날짜 표시: `$content->getDate()` (그대로 유지)
   - 작성자 표시: 
     - 예시 (Avatar 스킨): `<?php echo $content->getUserDisplay(sprintf('%s<br>%s', get_avatar(...), $content->getUserName()))?>`
     - **주의**: 각 스킨마다 작성자를 표시하는 방식(줄바꿈, 이미지 포함 여부 등)이 다릅니다. 이 부분은 **절대 수정하지 말고 원본 코드를 그대로 사용**하십시오.

## 5. 작업 체크리스트

1. [ ] **⭐⭐ CSS 프리픽스 확인 ⭐⭐**: `#kboard-default`가 완전히 제거되었는가? → `#kboard-[new-skin]`으로 완벽히 변경되었는가? (하나라도 남으면 스타일 충돌!)
2. [ ] **CSS 구조 확인**: `default` 스킨의 CSS 순서와 구조가 동일한가? (Diff 비교 가능)
3. [ ] **아이콘 확인**: Lock, Reply 아이콘이 깨지지 않고 벡터로 잘 나오는가?
4. [ ] **PC vs Mobile 정책 검증 (반응형)**:
    - **PC View**:
        - 버튼 그룹이 한 줄에 깔끔하게 정렬되는가?
        - 불필요한 줄바꿈이 없는가?
    - **Mobile View (@media max-width: 600px)**:
        - **버튼 그룹**(`.kboard-control`): 화면 폭이 좁을 때 버튼이 짤리지 않고 자연스럽게 **줄바꿈(Wrap)**되는가?
        - **네비게이션**(`.kboard-document-navi`): 이전글/다음글 링크는 **줄바꿈되지 않고** 항상 50:50 비율로 같은 줄에 있는가?
5. [ ] **로직 검증**: 원본 스킨의 고유 기능(예: 아바타 출력, 썸네일 출력)이 누락되지 않았는가?

## 6. 문서 유지보수 (Maintenance)

- **스킨 업데이트 대응**: KBoard 코어 업데이트나 `default` 스킨의 대대적인 리뉴얼이 발생하여 스타일 정책이나 파일 구조가 변경될 경우, **본 가이드(`AGENTS.md`)도 반드시 최신 상태로 업데이트**하십시오.
- **문서 동기화**: 본 문서의 내용은 실제 적용된 최신 스킨(`avatar` 등)의 상태와 항상 일치해야 합니다.
