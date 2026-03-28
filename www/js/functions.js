function setPointer(theRow, theRowNum, theAction, theDefaultColor, thePointerColor, theMarkColor)
{
    var theCells = null;

    // 1. Pointer and mark feature are disabled or the browser can't get the
    //    row -> exits
    if ((thePointerColor == '' && theMarkColor == '')
        || typeof(theRow.style) == 'undefined') {
        return false;
    }

    // 2. Gets the current row and exits if the browser can't get it
    if (typeof(document.getElementsByTagName) != 'undefined') {
        theCells = theRow.getElementsByTagName('td');
    }
    else if (typeof(theRow.cells) != 'undefined') {
        theCells = theRow.cells;
    }
    else {
        return false;
    }

    // 3. Gets the current color...
    var rowCellsCnt  = theCells.length;
    var domDetect    = null;
    var currentColor = null;
    var newColor     = null;
    // 3.1 ... with DOM compatible browsers except Opera that does not return
    //         valid values with "getAttribute"
    if (typeof(window.opera) == 'undefined'
        && typeof(theCells[0].getAttribute) != 'undefined') {
        currentColor = theCells[0].getAttribute('bgcolor');
        domDetect    = true;
    }
    // 3.2 ... with other browsers
    else {
        currentColor = theCells[0].style.backgroundColor;
        domDetect    = false;
    } // end 3

    // 4. Defines the new color
    // 4.1 Current color is the default one
    if (currentColor == ''
        || currentColor.toLowerCase() == theDefaultColor.toLowerCase()) {
        if (theAction == 'over' && thePointerColor != '') {
            newColor              = thePointerColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor              = theMarkColor;
            marked_row[theRowNum] = true;
        }
    }
    // 4.1.2 Current color is the pointer one
    else if (currentColor.toLowerCase() == thePointerColor.toLowerCase()
             && (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])) {
        if (theAction == 'out') {
            newColor              = theDefaultColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor              = theMarkColor;
            marked_row[theRowNum] = true;
        }
    }
    // 4.1.3 Current color is the marker one
    else if (currentColor.toLowerCase() == theMarkColor.toLowerCase()) {
        if (theAction == 'click') {
            newColor              = (thePointerColor != '')
                                  ? thePointerColor
                                  : theDefaultColor;
            marked_row[theRowNum] = (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])
                                  ? true
                                  : null;
        }
    } // end 4

    // 5. Sets the new color...
    if (newColor) {
        var c = null;
        // 5.1 ... with DOM compatible browsers except Opera
        if (domDetect) {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].setAttribute('bgcolor', newColor, 0);
            } // end for
        }
        // 5.2 ... with other browsers
        else {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].style.backgroundColor = newColor;
            }
        }
    } // end 5

    return true;
} // end of the 'setPointer()' function

function imgFit (img, maxImgWidth) 
{ 
   if (typeof img.naturalWidth == 'undefined') { 
      img.naturalHeight = img.height; 
      img.naturalWidth = img.width; 
   } 
   if (img.width > maxImgWidth) { 
      img.height = Math.round(((maxImgWidth)/img.width)*img.height); 
      img.width = maxImgWidth; 
      img.title = 'Нажмите на картинку для увеличения'; 
      img.style.cursor = 'move'; 
   } else if (img.width == maxImgWidth && img.width < img.naturalWidth) { 
      img.height = img.naturalHeight; 
      img.width = img.naturalWidth; 
      img.title = 'Нажмите на картинку для помещения в размер окна'; 
   } 
}

var tid = 0, x = 0, y = 0;
var obj;

document.onmousemove=track;

function track(e)
{
    x = (document.all) ? window.event.x + document.body.scrollLeft : e.pageX;
    y = (document.all) ? window.event.y + document.body.scrollTop : e.pageY;
}

function show(id)
{
    obj = document.getElementById(id);
    obj.style.left = x - 120;
    obj.style.top = y + 25;
    obj.style.display = "block";
    tid = window.setTimeout("show("+id+")",10);
}

function hide(id)
{
    obj = document.getElementById(id);
    window.clearTimeout(tid);
    obj.style.display = "none";
}
function show_hide(id)
{
        var klappText = document.getElementById('s' + id);
        var klappBild = document.getElementById('pic' + id);

        if (klappText.style.display == 'none') {
                  klappText.style.display = 'block';
                  klappBild.src = 'pic/minus.gif';
                  klappBild.title = 'Скрыть';
        } else {
                  klappText.style.display = 'none';
                  klappBild.src = 'pic/plus.gif';
                  klappBild.title = 'Показать';
        }
}


function updateText(id){
var txt = document.getElementById(id).value;
txt = txt.replace('Информация о фильме', '[u]Информация о фильме[/u]')
txt = txt.replace('Название:', '[b]Название: [/b]')
txt = txt.replace('Оригинальное название:', '[b]Оригинальное название: [/b]')
txt = txt.replace('Русское название:', '[b]Русское название: [/b]')
txt = txt.replace('Год выхода: ', '[b]Год выхода: [/b]')
txt = txt.replace('Жанр:', '[b]Жанр: [/b]')
txt = txt.replace('Режиссер:', '[b]Режиссер: [/b]')
txt = txt.replace('В ролях:', '[b]В ролях: [/b]')
txt = txt.replace('О фильме:', '[b]О фильме: [/b]')
txt = txt.replace('Выпущено:', '[b]Выпущено: [/b]')
txt = txt.replace('Продолжительность:', '[b]Продолжительность: [/b]')
txt = txt.replace('Перевод:', '[b]Перевод: [/b]')
txt = txt.replace('Субтитры:', '[b]Субтитры: [/b]')
txt = txt.replace('Дополнительно:', '[b]Дополнительно: [/b]')
txt = txt.replace('Файл', '[u]Файл[/u]')
txt = txt.replace('Формат:', '[b]Формат: [/b]')
txt = txt.replace('Качество:', '[b]Качество: [/b]')
txt = txt.replace('Видео:', '[b]Видео: [/b]')
txt = txt.replace('Звук:', '[b]Звук: [/b]')
txt = txt.replace('Исполнитель:', '[b]Исполнитель: [/b]')
txt = txt.replace('Альбом:', '[b]Альбом: [/b]')
txt = txt.replace('Треклист:', '[b][u]Треклист:[/u][/b]')
txt = txt.replace('Платформа:', '[b]Платформа: [/b]')
txt = txt.replace('Язык интерфейса:', '[b]Язык интерфейса: [/b]')
txt = txt.replace('Лекарство:', '[b]Лекарство: [/b]')
txt = txt.replace('Описание:', '[b]Описание: [/b]')
txt = txt.replace('Доп. информация:', '[b]Доп. информация: [/b]')
txt = txt.replace('Издательство:', '[b]Издательство: [/b]')
txt = txt.replace('Страниц:', '[b]Страниц: [/b]')
txt = txt.replace('Серия или Выпуск:', '[b]Серия или Выпуск: [/b]')
txt = txt.replace('Язык:', '[b]Язык: [/b]')
txt = txt.replace('О книге:', '[b][u]О книге:[/u][/b]')
txt = txt.replace('Об игре:', '[b]Об игре: [/b]')
txt = txt.replace('Особенности игры:', '[b]Особенности игры: [/b]')
txt = txt.replace('Системные требования:', '[b]Системные требования: [/b]')
txt = txt.replace('Тематика:', '[b]Тематика: [/b]')
txt = txt.replace('Формат(ы):', '[b]Формат(ы): [/b]')
txt = txt.replace('Количество:', '[b]Количество: [/b]')
txt = txt.replace('Минимальное разрешение:', '[b]Минимальное разрешение: [/b]')
txt = txt.replace('Максимальное разрешение:', '[b]Максимальное разрешение: [/b]')
txt = txt.replace('Продюсер:', '[b]Продюсер: [/b]')
txt = txt.replace('От издателя ', '[b]От издателя: [/b]')
txt = txt.replace('Звуковые дорожки:', '[b]Звуковые дорожки: [/b]')
txt = txt.replace('Дистрибьютор:', '[b]Дистрибьютор: [/b]')
txt = txt.replace('Региональный код:', '[b]Региональный код: [/b]')
txt = txt.replace('Размер:', '[b]Размер: [/b]')
txt = txt.replace('Страна:', '[b]Страна: [/b]')
txt = txt.replace('Год выпуска:', '[b]Год выпуска: [/b]')
txt = txt.replace('Трэклист:', '[u]Трэклист: [/u]')
txt = txt.replace('Видео кодек:', '[b]Видео кодек: [/b]')
txt = txt.replace('Аудио кодек:', '[b]Аудио кодек: [/b]')
txt = txt.replace('Аудио:', '[b]Аудио: [/b]')
txt = txt.replace('Автор:', '[b]Автор: [/b]')
txt = txt.replace('Видеокодек:', '[b]Видеокодек: [/b]')
txt = txt.replace('Битрейт видео:', '[b]Битрейт видео: [/b]')
txt = txt.replace('Размер кадра:', '[b]Размер кадра: [/b]')
txt = txt.replace('Качество видео: ', '[b]Качество видео:  [/b]')
txt = txt.replace('Аудиокодек:', '[b]Аудиокодек: [/b]')
txt = txt.replace('Битрейт аудио:', '[b]Битрейт аудио: [/b]')
txt = txt.replace('Длина видео:', '[b]Длина видео: [/b]')
txt = txt.replace('Описание фильма:', '[b]Описание фильма: [/b]')
txt = txt.replace('IMDB', '[b][url=http://www.imdb.com]IMDB[/url][/b]')
document.getElementById(id).value = txt;
}

function changeText(text, id){
document.getElementById(id).value = text;
}


var azWin = '     Ё               ё       АБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдежзийклмнопрстуфхцчшщъыьэюя'
var azKoi = 'ё                Ё           юабцдефгхийклмнопярстужвьызшэщчъЮАБЦДЕФГХИЙКЛМНОПЯРСТУЖВЬЫЗШЭЩЧЪ'
var AZ=azWin
var azURL = '0123456789ABCDEF'
var b64s  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'
var b64a  = b64s.split('')
function enBASE64(str) {
  var a=Array(), i
  for( i=0; i<str.length; i++ ){
    var cch=str.charCodeAt(i)
    if( cch>127 ){  cch=AZ.indexOf(str.charAt(i))+163; if(cch<163) continue; }
    a.push(cch)
  };
  var s=Array(), lPos = a.length - a.length % 3
  for(i=0;i<lPos;i+=3){
    var t=(a[i]<<16)+(a[i+1]<<8)+a[i+2]
    s.push( b64a[(t>>18)&0x3f]+b64a[(t>>12)&0x3f]+b64a[(t>>6)&0x3f]+b64a[t&0x3f] )
  }
  switch ( a.length-lPos ) {
    case 1 : var t=a[lPos]<<4; s.push(b64a[(t>>6)&0x3f]+b64a[t&0x3f]+'=='); break
    case 2 : var t=(a[lPos]<<10)+(a[lPos+1]<<2); s.push(b64a[(t>>12)&0x3f]+b64a[(t>>6)&0x3f]+b64a[t&0x3f]+'='); break
  }
  return s.join('')
}
function deBASE64(str) {
  while(str.substr(-1,1)=='=')str=str.substr(0,str.length-1);
  var b=str.split(''), i
  var s=Array(), t
  var lPos = b.length - b.length % 4
  for(i=0;i<lPos;i+=4){
    t=(b64s.indexOf(b[i])<<18)+(b64s.indexOf(b[i+1])<<12)+(b64s.indexOf(b[i+2])<<6)+b64s.indexOf(b[i+3])
    s.push( ((t>>16)&0xff), ((t>>8)&0xff), (t&0xff) )
  }
  if( (b.length-lPos) == 2 ){ t=(b64s.indexOf(b[lPos])<<18)+(b64s.indexOf(b[lPos+1])<<12); s.push( ((t>>16)&0xff)); }
  if( (b.length-lPos) == 3 ){ t=(b64s.indexOf(b[lPos])<<18)+(b64s.indexOf(b[lPos+1])<<12)+(b64s.indexOf(b[lPos+2])<<6); s.push( ((t>>16)&0xff), ((t>>8)&0xff) ); }
  for( i=s.length-1; i>=0; i-- ){
    if( s[i]>=168 ) s[i]=AZ.charAt(s[i]-163)
    else s[i]=String.fromCharCode(s[i])
  };
  return s.join('')
}

function placeholderSetup(id) {
	var el = ge(id);
	if(!el) return;
	if(el.type != 'text') return;
	if(el.type != 'text') return;

	var ph = el.getAttribute("placeholder");
	if( ph && ph != "" ) {
		el.value = ph;
		el.style.color = '#777';
		el.is_focused = 0;
		el.onfocus = placeholderFocus;
		el.onblur = placeholderBlur;
	}
}

function placeholderFocus() {
  if(!this.is_focused) {
    this.is_focused = 1;
    this.value = '';
    this.style.color = '#000';

    var rs = this.getAttribute("radioselect");
    if( rs && rs != "" ) {
      var re = document.getElementById(rs);
      if(!re) { return; }
      if(re.type != 'radio') return;

      re.checked=true;
    }
  }
}

function placeholderBlur() {
  var ph = this.getAttribute("placeholder")
  if( this.is_focused && ph && this.value == "" ) {
		this.is_focused = 0;
    this.value = ph;
    this.style.color = '#777';
  }
}
