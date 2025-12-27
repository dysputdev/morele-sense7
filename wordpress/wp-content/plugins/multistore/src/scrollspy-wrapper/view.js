class ScrollSpy {
  constructor(wrapper) {
    this.wrapper = wrapper;
    this.nav = wrapper.querySelector('.multistore-block-scrollspy-nav');
    this.sections = wrapper.querySelectorAll('.multistore-block-scrollspy-section');
    this.offset = parseInt(wrapper.dataset.scrollspyOffset) || 100;
    this.activeClass = wrapper.dataset.scrollspyActiveClass || 'is-active';
    this.currentActive = null;
    
    this.init();
  }
  
  init() {
    if (!this.nav || this.sections.length === 0) return;
    
    this.updateNav();
    this.handleScroll();
    
    window.addEventListener('scroll', () => this.handleScroll(), { passive: true });
    
    // Smooth scroll for nav links
    this.nav.querySelectorAll('a[href^="#"]').forEach(link => {
      link.addEventListener('click', (e) => this.smoothScroll(e));
    });
  }
  
  updateNav() {
    const navList = this.nav.querySelector('.multistore-block-scrollspy-nav__list');
    navList.innerHTML = '';
    
    this.sections.forEach(section => {
      const li = document.createElement('li');
      li.className = 'multistore-block-scrollspy-nav__item';
      
      const a = document.createElement('a');
      a.href = `#${section.id}`;
      a.className = 'multistore-block-scrollspy-nav__link';
      a.textContent = section.dataset.label || section.id;
      
      li.appendChild(a);
      navList.appendChild(li);
    });
  }
  
  handleScroll() {
    const scrollPos = window.scrollY + this.offset;
    let activeSection = null;
    
    this.sections.forEach(section => {
      const sectionTop = section.offsetTop;
      const sectionBottom = sectionTop + section.offsetHeight;
      
      if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
        activeSection = section;
      }
    });
    
    if (activeSection && activeSection !== this.currentActive) {
      this.setActive(activeSection);
    }
  }
  
  setActive(section) {
    // Remove old active class
    this.nav.querySelectorAll('.multistore-block-scrollspy-nav__link').forEach(link => {
      link.classList.remove(this.activeClass);
    });
    
    // Add new active class
    const activeLink = this.nav.querySelector(`a[href="#${section.id}"]`);
    if (activeLink) {
      activeLink.classList.add(this.activeClass);
    }
    
    this.currentActive = section;
  }
  
  smoothScroll(e) {
    e.preventDefault();
    const targetId = e.target.getAttribute('href').slice(1);
    const target = document.getElementById(targetId);
    
    if (target) {
      const targetPos = target.offsetTop - this.offset + 20;
      window.scrollTo({
        top: targetPos,
        behavior: 'smooth'
      });
    }
  }
}

// Initialize all ScrollSpy instances
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.multistore-block-scrollspy-wrapper').forEach(wrapper => {
    new ScrollSpy(wrapper);
  });
});