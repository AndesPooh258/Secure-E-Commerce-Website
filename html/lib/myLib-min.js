(function(){function h(b,a){alert("FieldError: "+a);b.focus();return!1}String.prototype.escapeHTML=function(){return this.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;")};var f=window.myLib=window.myLib||{};f.formData=function(b){this.data=[];for(var a=0,d=b.elements;b=d[a];a++)!b.disabled&&""!=b.name&&("radio"!=b.type&&"checkbox"!=b.type||b.checked)&&this.append(b.name,b.value)};f.formData.prototype={toString:function(){return this.data.join("&")},append:function(b,a){this.data.push(encodeURIComponent(b)+
"="+encodeURIComponent(a))}};f.ajax=function(b){b=b||{};var a=window.XMLHttpRequest?new XMLHttpRequest:new ActiveXObject("Microsoft.XMLHTTP"),d=b.async||!0,c=b.success||null,g=b.error||function(){alert("AJAX Error: "+this.status)};a.open(b.method||"GET",b.url||"",d);"POST"==b.method&&a.setRequestHeader("Content-Type","application/x-www-form-urlencoded");d&&(a.onreadystatechange=function(){if(4==a.readyState){var e=a.status;200<=e&&300>e||304==e||1223==e?c&&c.call(a,a.responseText):500<=e&&g.call(a)}});
a.onerror=function(){g.call(a)};a.send(b.data||null);!d&&callback&&callback.call(a,a.responseText)};f.validate=function(b){for(var a=0,d,c,g=b.elements;c=g[a];a++){if(c.hasAttribute("required"))if("radio"==c.type){if(k&&k==c.name)continue;for(var e=0,l=!1,k=c.name,n=b[k],m;m=n[e];e++)if(m.checked){l=!0;break}if(!l)return h(c,"choose a "+c.name);continue}else if("checkbox"==c.type&&!c.checked||""==c.value)return h(c,c.name+" is required");if((d=c.getAttribute("pattern"))&&!(new RegExp(d)).test(c.value))return h(c,
"invalid "+c.name)}return!0};f.submitOverAJAX=function(b,a){var d=new f.formData(b);d.append("rnd",(new Date).getTime());a=a||{};a.url=a.url||b.getAttribute("action");a.method=a.method||"POST";a.data=d.toString();a.success=a.success||function(c){alert(c)};console.log(a);f.ajax(a)}})();