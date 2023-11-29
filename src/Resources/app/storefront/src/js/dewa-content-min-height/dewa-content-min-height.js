import Plugin from 'src/plugin-system/plugin.class';

export default class DewaContentMinHeight extends Plugin {
    init() {
        this.calculateContentHeight();
    }

   calculateContentHeight(){
        let header = document.querySelector("header");
        let footer = document.querySelector("footer");
        let windowHeight = window.innerHeight;
        let headerHeight = header.offsetHeight;
        let footerHeight = footer.offsetHeight;
        let contentHeight = windowHeight - headerHeight - footerHeight;
        let elements = document.querySelectorAll(".container-main,[data-dewa-content-min-height]");

        elements.forEach((element) => {
            element.style.minHeight = Math.round(contentHeight)+"px";
        })
   }
}
