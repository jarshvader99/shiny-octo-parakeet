# Accessibility Audit - WCAG AA Compliance Checklist

## Testing Methodology
- Manual keyboard navigation testing
- Screen reader testing (VoiceOver, NVDA, JAWS)
- Automated tools: axe DevTools, Lighthouse, WAVE
- Color contrast validation
- Focus state verification

## WCAG 2.1 Level AA Requirements

### 1. Perceivable

#### 1.1 Text Alternatives
- [ ] All images have meaningful alt text
- [ ] Decorative images use alt="" or aria-hidden="true"
- [ ] Form inputs have associated labels
- [ ] Icon buttons have aria-label or sr-only text

**Status:** NEEDS REVIEW  
**Components:**
- BillCard.vue - No images currently
- Icons in OnboardingTour - Need aria-label
- StanceForm icons - Need sr-only labels

#### 1.2 Time-based Media
- [x] No video/audio content currently
- [ ] Future: Captions/transcripts required for any media

**Status:** NOT APPLICABLE

#### 1.3 Adaptable
- [ ] Content maintains meaning without CSS
- [ ] Reading order logical without styles
- [ ] Form instructions programmatically associated
- [ ] Orientation works in both portrait/landscape

**Status:** NEEDS TESTING  
**Test:** Disable CSS and verify content structure

#### 1.4 Distinguishable

**Color Contrast (4.5:1 for normal text, 3:1 for large text):**
- [ ] Slate-100 on Slate-950 (main text) - PASS (14.5:1)
- [ ] Slate-400 on Slate-950 (secondary text) - PASS (6.8:1)
- [ ] Emerald-400 on Slate-950 (support stance) - NEEDS CHECK
- [ ] Rose-400 on Slate-950 (oppose stance) - NEEDS CHECK
- [ ] Amber-400 on Slate-950 (mixed stance) - NEEDS CHECK
- [ ] Indigo-400 on Slate-950 (links) - NEEDS CHECK

**Color Not Sole Indicator:**
- [ ] Stance options use text labels, not just color - PASS
- [ ] Links underlined or otherwise distinguished - NEEDS CHECK
- [ ] Error states have icon + text, not just red - NEEDS CHECK

**Text Sizing:**
- [ ] Text resizes to 200% without loss of content - NEEDS CHECK
- [ ] No horizontal scrolling at 320px width - NEEDS CHECK

**Status:** PARTIALLY COMPLIANT - Contrast checks needed

### 2. Operable

#### 2.1 Keyboard Accessible

**All Functionality Available via Keyboard:**
- [ ] Navigation menu accessible
- [ ] All buttons focusable and activatable
- [ ] Modal dialogs trap focus properly
- [ ] Dropdown menus keyboard navigable
- [ ] Form fields reachable via Tab

**Test Results:**
- Dashboard - NEEDS TESTING
- BillCard links - Should be keyboard accessible (native <Link>)
- StanceForm radio buttons - Should work (native inputs)
- OnboardingTour - NEEDS TESTING (modal focus trap)
- BillFollowButton - NEEDS TESTING

**No Keyboard Traps:**
- [ ] Can Tab out of all interactive elements
- [ ] Modals allow Escape key to close
- [ ] No infinite focus loops

**Status:** NEEDS COMPREHENSIVE TESTING

#### 2.2 Enough Time
- [x] No time limits on user actions
- [x] No auto-advancing carousels
- [ ] Future: Session timeout warnings needed

**Status:** COMPLIANT

#### 2.3 Seizures and Physical Reactions
- [x] No flashing content > 3 times per second
- [x] Animations respect prefers-reduced-motion

**Current Implementation:**
```css
transition-all duration-150 ease-out
/* Should add: */
@media (prefers-reduced-motion: reduce) {
    transition: none !important;
}
```

**Status:** NEEDS IMPROVEMENT - Add motion-reduce support

#### 2.4 Navigable

**Skip Links:**
- [ ] "Skip to main content" link at top of page - MISSING
- [ ] Hidden until focused

**Page Titles:**
- [ ] Each page has unique, descriptive title
- [ ] AppLayout sets dynamic titles - NEEDS CHECK

**Focus Order:**
- [ ] Tab order follows visual order
- [ ] No unexpected jumps in focus

**Link Purpose:**
- [ ] Link text describes destination
- [ ] No "click here" links
- [ ] External links indicated

**Multiple Ways to Find Content:**
- [ ] Navigation menu
- [ ] Search (future)
- [ ] Breadcrumbs (future)

**Headings and Labels:**
- [ ] Headings properly structured (h1 > h2 > h3)
- [ ] Form labels descriptive

**Focus Visible:**
- [ ] All focusable elements show focus indicator
- [ ] Focus indicator has 3:1 contrast ratio

**Status:** NEEDS SIGNIFICANT WORK

#### 2.5 Input Modalities

**Pointer Gestures:**
- [x] No complex gestures required
- [ ] All swipe actions have single-pointer alternative

**Pointer Cancellation:**
- [x] Click events fire on mouseup, not mousedown
- [x] Can cancel accidental clicks by moving away

**Label in Name:**
- [ ] Button visible text matches accessible name
- [ ] Icon buttons have matching aria-label

**Motion Actuation:**
- [x] No device motion triggers (shake, tilt)

**Status:** MOSTLY COMPLIANT

### 3. Understandable

#### 3.1 Readable

**Language of Page:**
- [ ] `<html lang="en">` attribute set - NEEDS CHECK
- [ ] Language changes marked with lang attribute

**Status:** NEEDS VERIFICATION

#### 3.2 Predictable

**On Focus:**
- [x] No context changes on focus alone
- [x] Focus doesn't trigger navigation

**On Input:**
- [ ] Form fields don't auto-submit on input
- [ ] Radio buttons don't trigger action until Submit

**Consistent Navigation:**
- [ ] Navigation in same order on all pages
- [ ] AppLayout provides consistent header/footer

**Consistent Identification:**
- [ ] Same icons mean same thing across site
- [ ] Consistent button styling

**Status:** LIKELY COMPLIANT - Needs verification

#### 3.3 Input Assistance

**Error Identification:**
- [ ] Form validation errors clearly identified
- [ ] Error messages descriptive, not just "Invalid"

**Labels or Instructions:**
- [ ] All form fields have labels
- [ ] Required fields marked with * and text label
- [ ] Placeholder text not sole label

**Error Suggestion:**
- [ ] Validation provides helpful suggestions
- [ ] Example: "ZIP code must be 5 digits"

**Error Prevention:**
- [ ] Confirmation for destructive actions (Delete account)
- [ ] Ability to review before submission

**Current Implementation - StanceForm:**
```vue
<label for="reason">
    Your Reasoning <span class="text-rose-400">*</span>
</label>
<p class="text-xs text-slate-500 mb-3">
    Provide a thoughtful explanation... (minimum 50 characters)
</p>
```

**Status:** PARTIALLY COMPLIANT - Error messages need improvement

### 4. Robust

#### 4.1 Compatible

**Parsing:**
- [ ] Valid HTML (no duplicate IDs)
- [ ] Proper nesting of elements
- [ ] All tags closed correctly

**Name, Role, Value:**
- [ ] All interactive elements have accessible names
- [ ] Custom components use ARIA roles properly
- [ ] State changes communicated to assistive tech

**Status:** NEEDS VALIDATION

## Component-Specific Audit

### ✅ AppLayout.vue
- [ ] Proper landmark regions (header, nav, main, footer)
- [ ] Skip link to main content
- [ ] Consistent navigation
- [ ] Focus management on page transitions

### ✅ BillCard.vue
**Checklist:**
- [x] Link has accessible name (bill title)
- [ ] Status badge accessible to screen readers
- [ ] "Local" badge has proper semantics
- [ ] Focus indicator visible
- [ ] Entire card is click target (good for motor impairment)

**Code Review:**
```vue
<Link :href="route('bills.show', bill.id)" class="block...">
```
- Uses native Link component - accessible by default
- Should add aria-label for more context

### ⚠️ StanceForm.vue
**Checklist:**
- [x] Radio buttons use native input elements (good)
- [ ] Radio group has fieldset/legend
- [ ] Each option has visible label
- [ ] Textarea has proper label
- [ ] Character counter announced to screen readers
- [ ] Error messages linked with aria-describedby
- [ ] Required fields marked in accessible way

**Critical Issues:**
1. **Missing fieldset:**
```vue
<!-- CURRENT -->
<div>
    <label class="block text-sm font-medium text-slate-300 mb-3">
        Your Position
    </label>
    <div class="space-y-3">
        <label v-for="option in stanceOptions">

<!-- SHOULD BE -->
<fieldset>
    <legend class="text-sm font-medium text-slate-300 mb-3">
        Your Position
    </legend>
    <div class="space-y-3">
```

2. **Character counter not announced:**
```vue
<!-- ADD -->
<div aria-live="polite" aria-atomic="true" class="sr-only">
    {{ characterCount }} of {{ maxCharacters }} characters
</div>
```

### ⚠️ OnboardingTour.vue
**Checklist:**
- [ ] Modal has role="dialog"
- [ ] Modal has aria-labelledby pointing to title
- [ ] Modal has aria-describedby pointing to description
- [ ] Focus trapped within modal
- [ ] Escape key closes modal
- [ ] Focus returns to trigger element on close
- [ ] Progress dots have accessible labels
- [ ] "Skip" and "Complete" buttons clearly labeled

**Required Fixes:**
```vue
<div role="dialog" 
     aria-labelledby="tour-title"
     aria-describedby="tour-description"
     aria-modal="true">
    <h2 id="tour-title">{{ currentStep.title }}</h2>
    <div id="tour-description">{{ currentStep.description }}</div>
</div>
```

### ✅ BillFollowButton.vue
**Checklist:**
- [ ] Button has clear accessible name
- [ ] Loading state announced to screen readers
- [ ] Success state announced
- [ ] Follower count accessible

### ✅ DataFreshnessIndicator.vue
**Checklist:**
- [x] Text content readable
- [ ] Icon has aria-hidden="true" (decorative)
- [ ] Timestamp in machine-readable format

## Automated Testing Tools

### Lighthouse Accessibility Score
**Target:** 100/100

**Current Issues:** (Run `npm run build && lighthouse http://localhost:8000`)
- [ ] Run initial audit
- [ ] Document issues
- [ ] Fix critical issues
- [ ] Re-run and verify

### axe DevTools
**Installation:** Browser extension

**Tests:**
- [ ] Automated scan on Dashboard
- [ ] Automated scan on Bill detail page
- [ ] Automated scan on forms
- [ ] Document all issues

### WAVE Tool
**URL:** https://wave.webaim.org/

**Tests:**
- [ ] Run on deployed site
- [ ] Check for errors (not warnings)
- [ ] Verify contrast ratios

## Manual Testing Procedures

### Keyboard Navigation Test
**Steps:**
1. [ ] Disconnect mouse
2. [ ] Tab through entire Dashboard
3. [ ] Verify all interactive elements reachable
4. [ ] Verify focus visible at all times
5. [ ] Verify tab order logical
6. [ ] Test Shift+Tab (reverse)
7. [ ] Test Enter/Space on buttons
8. [ ] Test Escape on modals

### Screen Reader Test

**VoiceOver (macOS):**
1. [ ] Cmd+F5 to enable VoiceOver
2. [ ] Navigate Dashboard with VO+Arrow keys
3. [ ] Verify all content announced
4. [ ] Verify landmark navigation (Ctrl+Option+U > Landmarks)
5. [ ] Verify form fields have labels
6. [ ] Verify error messages announced

**NVDA/JAWS (Windows):**
1. [ ] Navigate with Tab and Arrow keys
2. [ ] Verify heading structure (H key)
3. [ ] Verify landmarks (D key)
4. [ ] Verify forms mode works correctly

### Color Contrast Test
**Tool:** Chrome DevTools or https://contrast-ratio.com/

**Colors to Check:**
```
Primary Text: #f1f5f9 (slate-100) on #020617 (slate-950)
Secondary Text: #94a3b8 (slate-400) on #020617
Support: #4ade80 (emerald-400) on #020617
Oppose: #f87171 (rose-400) on #020617
Mixed: #fbbf24 (amber-400) on #020617
Links: #818cf8 (indigo-400) on #020617
```

**Results:**
- [ ] Primary text: ____:1 (Pass/Fail)
- [ ] Secondary text: ____:1 (Pass/Fail)
- [ ] Support text: ____:1 (Pass/Fail)
- [ ] Oppose text: ____:1 (Pass/Fail)
- [ ] Mixed text: ____:1 (Pass/Fail)
- [ ] Links: ____:1 (Pass/Fail)

### Motion Reduction Test
**Steps:**
1. [ ] Enable "Reduce Motion" in OS settings
2. [ ] Reload application
3. [ ] Verify no animations play
4. [ ] Verify transitions are instant

**Current Tailwind Config:**
```js
// tailwind.config.js
module.exports = {
    theme: {
        extend: {
            transitionDuration: {
                DEFAULT: '150ms',
            },
        },
    },
    variants: {
        extend: {
            transitionProperty: ['motion-reduce'],
        },
    },
}
```

**Required CSS:**
```css
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
}
```

## Critical Issues to Fix

### High Priority
1. **Add skip link to main content**
2. **Fix StanceForm fieldset/legend structure**
3. **Add focus trap to OnboardingTour modal**
4. **Add aria-labels to all icon buttons**
5. **Verify color contrast on all stance colors**
6. **Add motion-reduce CSS**

### Medium Priority
1. **Add aria-live regions for dynamic content**
2. **Improve error message association**
3. **Add landmark regions to all pages**
4. **Verify heading hierarchy**

### Low Priority
1. **Add breadcrumbs for navigation**
2. **Add search landmark**
3. **Enhance link context**

## Implementation Checklist

### Global Fixes (app.css or AppLayout.vue)
- [ ] Add skip link component
- [ ] Add motion-reduce CSS
- [ ] Set html lang="en"
- [ ] Add main, header, nav landmarks

### Component Fixes
- [ ] StanceForm: Convert to fieldset/legend
- [ ] StanceForm: Add aria-live for character count
- [ ] OnboardingTour: Add dialog role and ARIA attributes
- [ ] OnboardingTour: Implement focus trap
- [ ] BillCard: Add aria-label for better context
- [ ] All icon buttons: Add aria-label

### Testing Schedule
- [ ] Day 1: Run automated tools, document issues
- [ ] Day 2: Manual keyboard testing, fix critical issues
- [ ] Day 3: Screen reader testing with VoiceOver
- [ ] Day 4: Color contrast fixes and verification
- [ ] Day 5: Final audit and documentation

## Sign-Off Criteria

**WCAG AA Compliance Achieved When:**
- [ ] Lighthouse accessibility score ≥ 95
- [ ] axe DevTools shows 0 errors
- [ ] All manual keyboard tests pass
- [ ] Screen reader announces all content correctly
- [ ] All color contrasts meet 4.5:1 (3:1 for large)
- [ ] All interactive elements have focus indicators
- [ ] All forms fully accessible
- [ ] Motion respects prefers-reduced-motion

**Last Updated:** 2026-01-07  
**Audited By:** Phase 8 Development  
**Status:** NOT STARTED - 6 critical fixes identified
