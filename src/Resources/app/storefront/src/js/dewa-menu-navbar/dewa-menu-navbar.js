import Plugin from 'src/plugin-system/plugin.class';
import { tns } from 'tiny-slider/src/tiny-slider.module';

export default class DewaMenuNavbar extends Plugin {

    static options = {};

    init() {
        this._menuSlider = tns({
            container: this.el,
            navPosition: 'bottom',
            gutter:10,
            speed: 500,
            nav: false,
            controls: true,
            autoWidth: true,
            autoHeight: true,
            loop:false,
            controlsContainer: ".menu-slider-controls",
            onInit: this.slideInit()
        });

        this._menuNavbar = document.getElementById('menuNavbar');

        this._navbarOffsetSpace = this._menuNavbar.offsetHeight + 30;

        this.menuNavbar();
    }

    slideInit(){
        let menuNav = document.querySelector('.dewa-menu-navigation');

        setTimeout(function() {
            menuNav.classList.add('initialized');
        }, 400);

    }

    menuNavbar() {

        const that = this;

        if(that._menuNavbar){
            this.makeStickyNavigation();
            this.makeNavLinksSmooth();
            this.makeUrlChangeToMenuItem();
            this.makeScrollspy();
        }
    }

    makeScrollspy(){
        const that = this;
        let timer;
        let menuSection = document.querySelectorAll('.dewa-menu-navigation .tns-item');

        menuSection.forEach(v=> {

            v.onclick = (()=> {
                setTimeout(()=> {
                    menuSection.forEach(j=> j.classList.remove('current'))
                    v.classList.add('current');
                },300)
            })
        })

        window.addEventListener("scroll", function (){
            let mainSection = document.querySelectorAll('.dewa-menu-category');

            mainSection.forEach((v,i)=> {
                let elementDetector = v.offsetTop - (that._navbarOffsetSpace);

                if(window.pageYOffset >= elementDetector){
                    menuSection.forEach(v=>{
                        v.classList.remove('current');
                    })
                    menuSection[i].classList.add('current');

                    if(timer) {
                        window.clearTimeout(timer);
                    }

                    timer = window.setTimeout(function() {
                        that._menuSlider.goTo(i);
                    }, 400);
                }
            })
        })
    }

    makeStickyNavigation(){
        const that = this;
        var sticky = that._menuNavbar.offsetTop;

        window.addEventListener("scroll", function (){
            if (window.pageYOffset >= sticky) {
                that._menuNavbar.classList.add('sticky');
                that._menuNavbar.classList.add('shadow-sm');
                $('.dewa-menu-content').css('margin-top', that._menuNavbar.offsetHeight);
            }
            if(window.pageYOffset <= sticky){
                that._menuNavbar.classList.remove('sticky');
                that._menuNavbar.classList.remove('shadow-sm');
                $('.dewa-menu-content').css('margin-top', '');
            }
        });
    }

    makeNavLinksSmooth(){
        const that = this;
        const navLinks = document.querySelectorAll( '.dewa-menu.nav-link' );

        for ( let n in navLinks ) {
            if ( navLinks.hasOwnProperty( n ) ) {
                navLinks[ n ].addEventListener( 'click', e => {
                    e.preventDefault( );
                    var selector = document.querySelector( navLinks[ n ].hash ).offsetTop;
                    selector = selector - (that._navbarOffsetSpace);

                    window.scrollTo( {
                        top: selector,
                        behavior: "smooth"
                    } );
                } );
            }
        }
    }

    makeUrlChangeToMenuItem(){
        const that = this;
        const sections = document.querySelectorAll( '.dewa-menu-category' );
        const offset = that._navbarOffsetSpace;
        let timer;

        window.addEventListener("scroll",function (){
            if(timer) {
                window.clearTimeout(timer);
            }

            timer = window.setTimeout(function() {
                const scrollPos = document.documentElement.scrollTop || document.body.scrollTop;

                var url;
                var hostname;
                var pathname;
                var baseUrl;

                for ( let s in sections )
                    if ( sections.hasOwnProperty( s ) && sections[ s ].offsetTop - offset <= scrollPos ) {
                        const id = sections[ s ].id;

                        hostname = window.location.origin;
                        pathname = window.location.pathname;
                        baseUrl = hostname + pathname;

                        url = new URL(baseUrl);
                        url = url + '#' + id;
                        window.history.replaceState(null, null, url);
                    }
            }, 100);
        });
    }
}
