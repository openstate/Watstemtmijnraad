//dragdrop.js
if(Object.isUndefined(Effect))throw("dragdrop.js requires including script.aculo.us' effects.js library");var Droppables={drops:[],remove:function(a){this.drops=this.drops.reject(function(d){return d.element==$(a)})},add:function(a){a=$(a);var b=Object.extend({greedy:true,hoverclass:null,tree:false},arguments[1]||{});if(b.containment){b._containers=[];var d=b.containment;if(Object.isArray(d)){d.each(function(c){b._containers.push($(c))})}else{b._containers.push($(d))}}if(b.accept)b.accept=[b.accept].flatten();Element.makePositioned(a);b.element=a;this.drops.push(b)},findDeepestChild:function(a){deepest=a[0];for(i=1;i<a.length;++i)if(Element.isParent(a[i].element,deepest.element))deepest=a[i];return deepest},isContained:function(a,b){var d;if(b.tree){d=a.treeNode}else{d=a.parentNode}return b._containers.detect(function(c){return d==c})},isAffected:function(a,b,c){return((c.element!=b)&&((!c._containers)||this.isContained(b,c))&&((!c.accept)||(Element.classNames(b).detect(function(v){return c.accept.include(v)})))&&Position.within(c.element,a[0],a[1]))},deactivate:function(a){if(a.hoverclass)Element.removeClassName(a.element,a.hoverclass);this.last_active=null},activate:function(a){if(a.hoverclass)Element.addClassName(a.element,a.hoverclass);this.last_active=a},show:function(b,c){if(!this.drops.length)return;var d,affected=[];this.drops.each(function(a){if(Droppables.isAffected(b,c,a))affected.push(a)});if(affected.length>0)d=Droppables.findDeepestChild(affected);if(this.last_active&&this.last_active!=d)this.deactivate(this.last_active);if(d){Position.within(d.element,b[0],b[1]);if(d.onHover)d.onHover(c,d.element,Position.overlap(d.overlap,d.element));if(d!=this.last_active)Droppables.activate(d)}},fire:function(a,b){if(!this.last_active)return;Position.prepare();if(this.isAffected([Event.pointerX(a),Event.pointerY(a)],b,this.last_active))if(this.last_active.onDrop){this.last_active.onDrop(b,this.last_active.element,a);return true}},reset:function(){if(this.last_active)this.deactivate(this.last_active)}};var Draggables={drags:[],observers:[],register:function(a){if(this.drags.length==0){this.eventMouseUp=this.endDrag.bindAsEventListener(this);this.eventMouseMove=this.updateDrag.bindAsEventListener(this);this.eventKeypress=this.keyPress.bindAsEventListener(this);Event.observe(document,"mouseup",this.eventMouseUp);Event.observe(document,"mousemove",this.eventMouseMove);Event.observe(document,"keypress",this.eventKeypress)}this.drags.push(a)},unregister:function(a){this.drags=this.drags.reject(function(d){return d==a});if(this.drags.length==0){Event.stopObserving(document,"mouseup",this.eventMouseUp);Event.stopObserving(document,"mousemove",this.eventMouseMove);Event.stopObserving(document,"keypress",this.eventKeypress)}},activate:function(a){if(a.options.delay){this._timeout=setTimeout(function(){Draggables._timeout=null;window.focus();Draggables.activeDraggable=a}.bind(this),a.options.delay)}else{window.focus();this.activeDraggable=a}},deactivate:function(){this.activeDraggable=null},updateDrag:function(a){if(!this.activeDraggable)return;var b=[Event.pointerX(a),Event.pointerY(a)];if(this._lastPointer&&(this._lastPointer.inspect()==b.inspect()))return;this._lastPointer=b;this.activeDraggable.updateDrag(a,b)},endDrag:function(a){if(this._timeout){clearTimeout(this._timeout);this._timeout=null}if(!this.activeDraggable)return;this._lastPointer=null;this.activeDraggable.endDrag(a);this.activeDraggable=null},keyPress:function(a){if(this.activeDraggable)this.activeDraggable.keyPress(a)},addObserver:function(a){this.observers.push(a);this._cacheObserverCallbacks()},removeObserver:function(a){this.observers=this.observers.reject(function(o){return o.element==a});this._cacheObserverCallbacks()},notify:function(a,b,c){if(this[a+'Count']>0)this.observers.each(function(o){if(o[a])o[a](a,b,c)});if(b.options[a])b.options[a](b,c)},_cacheObserverCallbacks:function(){['onStart','onEnd','onDrag'].each(function(a){Draggables[a+'Count']=Draggables.observers.select(function(o){return o[a]}).length})}};var Draggable=Class.create({initialize:function(e){var f={handle:false,reverteffect:function(a,b,c){var d=Math.sqrt(Math.abs(b^2)+Math.abs(c^2))*0.02;new Effect.Move(a,{x:-c,y:-b,duration:d,queue:{scope:'_draggable',position:'end'}})},endeffect:function(a){var b=Object.isNumber(a._opacity)?a._opacity:1.0;new Effect.Opacity(a,{duration:0.2,from:0.7,to:b,queue:{scope:'_draggable',position:'end'},afterFinish:function(){Draggable._dragging[a]=false}})},zindex:1000,revert:false,quiet:false,scroll:false,scrollSensitivity:20,scrollSpeed:15,snap:false,delay:0};if(!arguments[1]||Object.isUndefined(arguments[1].endeffect))Object.extend(f,{starteffect:function(a){a._opacity=Element.getOpacity(a);Draggable._dragging[a]=true;new Effect.Opacity(a,{duration:0.2,from:a._opacity,to:0.7})}});var g=Object.extend(f,arguments[1]||{});this.element=$(e);if(g.handle&&Object.isString(g.handle))this.handle=this.element.down('.'+g.handle,0);if(!this.handle)this.handle=$(g.handle);if(!this.handle)this.handle=this.element;if(g.scroll&&!g.scroll.scrollTo&&!g.scroll.outerHTML){g.scroll=$(g.scroll);this._isScrollChild=Element.childOf(this.element,g.scroll)}Element.makePositioned(this.element);this.options=g;this.dragging=false;this.eventMouseDown=this.initDrag.bindAsEventListener(this);Event.observe(this.handle,"mousedown",this.eventMouseDown);Draggables.register(this)},destroy:function(){Event.stopObserving(this.handle,"mousedown",this.eventMouseDown);Draggables.unregister(this)},currentDelta:function(){return([parseInt(Element.getStyle(this.element,'left')||'0'),parseInt(Element.getStyle(this.element,'top')||'0')])},initDrag:function(a){if(!Object.isUndefined(Draggable._dragging[this.element])&&Draggable._dragging[this.element])return;if(Event.isLeftClick(a)){var b=Event.element(a);if((tag_name=b.tagName.toUpperCase())&&(tag_name=='INPUT'||tag_name=='SELECT'||tag_name=='OPTION'||tag_name=='BUTTON'||tag_name=='TEXTAREA'))return;var c=[Event.pointerX(a),Event.pointerY(a)];var d=Position.cumulativeOffset(this.element);this.offset=[0,1].map(function(i){return(c[i]-d[i])});Draggables.activate(this);Event.stop(a)}},startDrag:function(a){this.dragging=true;if(!this.delta)this.delta=this.currentDelta();if(this.options.zindex){this.originalZ=parseInt(Element.getStyle(this.element,'z-index')||0);this.element.style.zIndex=this.options.zindex}if(this.options.ghosting){this._clone=this.element.cloneNode(true);this._originallyAbsolute=(this.element.getStyle('position')=='absolute');if(!this._originallyAbsolute)Position.absolutize(this.element);this.element.parentNode.insertBefore(this._clone,this.element)}if(this.options.scroll){if(this.options.scroll==window){var b=this._getWindowScroll(this.options.scroll);this.originalScrollLeft=b.left;this.originalScrollTop=b.top}else{this.originalScrollLeft=this.options.scroll.scrollLeft;this.originalScrollTop=this.options.scroll.scrollTop}}Draggables.notify('onStart',this,a);if(this.options.starteffect)this.options.starteffect(this.element)},updateDrag:function(a,b){if(!this.dragging)this.startDrag(a);if(!this.options.quiet){Position.prepare();Droppables.show(b,this.element)}Draggables.notify('onDrag',this,a);this.draw(b);if(this.options.change)this.options.change(this);if(this.options.scroll){this.stopScrolling();var p;if(this.options.scroll==window){with(this._getWindowScroll(this.options.scroll)){p=[left,top,left+width,top+height]}}else{p=Position.page(this.options.scroll);p[0]+=this.options.scroll.scrollLeft+Position.deltaX;p[1]+=this.options.scroll.scrollTop+Position.deltaY;p.push(p[0]+this.options.scroll.offsetWidth);p.push(p[1]+this.options.scroll.offsetHeight)}var c=[0,0];if(b[0]<(p[0]+this.options.scrollSensitivity))c[0]=b[0]-(p[0]+this.options.scrollSensitivity);if(b[1]<(p[1]+this.options.scrollSensitivity))c[1]=b[1]-(p[1]+this.options.scrollSensitivity);if(b[0]>(p[2]-this.options.scrollSensitivity))c[0]=b[0]-(p[2]-this.options.scrollSensitivity);if(b[1]>(p[3]-this.options.scrollSensitivity))c[1]=b[1]-(p[3]-this.options.scrollSensitivity);this.startScrolling(c)}if(Prototype.Browser.WebKit)window.scrollBy(0,0);Event.stop(a)},finishDrag:function(a,b){this.dragging=false;if(this.options.quiet){Position.prepare();var c=[Event.pointerX(a),Event.pointerY(a)];Droppables.show(c,this.element)}if(this.options.ghosting){if(!this._originallyAbsolute)Position.relativize(this.element);delete this._originallyAbsolute;Element.remove(this._clone);this._clone=null}var e=false;if(b){e=Droppables.fire(a,this.element);if(!e)e=false}if(e&&this.options.onDropped)this.options.onDropped(this.element);Draggables.notify('onEnd',this,a);var f=this.options.revert;if(f&&Object.isFunction(f))f=f(this.element);var d=this.currentDelta();if(f&&this.options.reverteffect){if(e==0||f!='failure')this.options.reverteffect(this.element,d[1]-this.delta[1],d[0]-this.delta[0])}else{this.delta=d}if(this.options.zindex)this.element.style.zIndex=this.originalZ;if(this.options.endeffect)this.options.endeffect(this.element);Draggables.deactivate(this);Droppables.reset()},keyPress:function(a){if(a.keyCode!=Event.KEY_ESC)return;this.finishDrag(a,false);Event.stop(a)},endDrag:function(a){if(!this.dragging)return;this.stopScrolling();this.finishDrag(a,true);Event.stop(a)},draw:function(a){var b=Position.cumulativeOffset(this.element);if(this.options.ghosting){var r=Position.realOffset(this.element);b[0]+=r[0]-Position.deltaX;b[1]+=r[1]-Position.deltaY}var d=this.currentDelta();b[0]-=d[0];b[1]-=d[1];if(this.options.scroll&&(this.options.scroll!=window&&this._isScrollChild)){b[0]-=this.options.scroll.scrollLeft-this.originalScrollLeft;b[1]-=this.options.scroll.scrollTop-this.originalScrollTop}var p=[0,1].map(function(i){return(a[i]-b[i]-this.offset[i])}.bind(this));if(this.options.snap){if(Object.isFunction(this.options.snap)){p=this.options.snap(p[0],p[1],this)}else{if(Object.isArray(this.options.snap)){p=p.map(function(v,i){return(v/this.options.snap[i]).round()*this.options.snap[i]}.bind(this))}else{p=p.map(function(v){return(v/this.options.snap).round()*this.options.snap}.bind(this))}}}var c=this.element.style;if((!this.options.constraint)||(this.options.constraint=='horizontal'))c.left=p[0]+"px";if((!this.options.constraint)||(this.options.constraint=='vertical'))c.top=p[1]+"px";if(c.visibility=="hidden")c.visibility=""},stopScrolling:function(){if(this.scrollInterval){clearInterval(this.scrollInterval);this.scrollInterval=null;Draggables._lastScrollPointer=null}},startScrolling:function(a){if(!(a[0]||a[1]))return;this.scrollSpeed=[a[0]*this.options.scrollSpeed,a[1]*this.options.scrollSpeed];this.lastScrolled=new Date();this.scrollInterval=setInterval(this.scroll.bind(this),10)},scroll:function(){var a=new Date();var b=a-this.lastScrolled;this.lastScrolled=a;if(this.options.scroll==window){with(this._getWindowScroll(this.options.scroll)){if(this.scrollSpeed[0]||this.scrollSpeed[1]){var d=b/1000;this.options.scroll.scrollTo(left+d*this.scrollSpeed[0],top+d*this.scrollSpeed[1])}}}else{this.options.scroll.scrollLeft+=this.scrollSpeed[0]*b/1000;this.options.scroll.scrollTop+=this.scrollSpeed[1]*b/1000}Position.prepare();Droppables.show(Draggables._lastPointer,this.element);Draggables.notify('onDrag',this);if(this._isScrollChild){Draggables._lastScrollPointer=Draggables._lastScrollPointer||$A(Draggables._lastPointer);Draggables._lastScrollPointer[0]+=this.scrollSpeed[0]*b/1000;Draggables._lastScrollPointer[1]+=this.scrollSpeed[1]*b/1000;if(Draggables._lastScrollPointer[0]<0)Draggables._lastScrollPointer[0]=0;if(Draggables._lastScrollPointer[1]<0)Draggables._lastScrollPointer[1]=0;this.draw(Draggables._lastScrollPointer)}if(this.options.change)this.options.change(this)},_getWindowScroll:function(w){var T,L,W,H;with(w.document){if(w.document.documentElement&&documentElement.scrollTop){T=documentElement.scrollTop;L=documentElement.scrollLeft}else if(w.document.body){T=body.scrollTop;L=body.scrollLeft}if(w.innerWidth){W=w.innerWidth;H=w.innerHeight}else if(w.document.documentElement&&documentElement.clientWidth){W=documentElement.clientWidth;H=documentElement.clientHeight}else{W=body.offsetWidth;H=body.offsetHeight}}return{top:T,left:L,width:W,height:H}}});Draggable._dragging={};var SortableObserver=Class.create({initialize:function(a,b){this.element=$(a);this.observer=b;this.lastValue=Sortable.serialize(this.element)},onStart:function(){this.lastValue=Sortable.serialize(this.element)},onEnd:function(){Sortable.unmark();if(this.lastValue!=Sortable.serialize(this.element))this.observer(this.element)}});var Sortable={SERIALIZE_RULE:/^[^_\-](?:[A-Za-z0-9\-\_]*)[_](.*)$/,sortables:{},_findRootElement:function(a){while(a.tagName.toUpperCase()!="BODY"){if(a.id&&Sortable.sortables[a.id])return a;a=a.parentNode}},options:function(a){a=Sortable._findRootElement($(a));if(!a)return;return Sortable.sortables[a.id]},destroy:function(a){var s=Sortable.options(a);if(s){Draggables.removeObserver(s.element);s.droppables.each(function(d){Droppables.remove(d)});s.draggables.invoke('destroy');delete Sortable.sortables[s.element.id]}},create:function(b){b=$(b);var c=Object.extend({element:b,tag:'li',dropOnEmpty:false,tree:false,treeTag:'ul',overlap:'vertical',constraint:'vertical',containment:b,handle:false,only:false,delay:0,hoverclass:null,ghosting:false,quiet:false,scroll:false,scrollSensitivity:20,scrollSpeed:15,format:this.SERIALIZE_RULE,elements:false,handles:false,onChange:Prototype.emptyFunction,onUpdate:Prototype.emptyFunction},arguments[1]||{});this.destroy(b);var d={revert:true,quiet:c.quiet,scroll:c.scroll,scrollSpeed:c.scrollSpeed,scrollSensitivity:c.scrollSensitivity,delay:c.delay,ghosting:c.ghosting,constraint:c.constraint,handle:c.handle};if(c.starteffect)d.starteffect=c.starteffect;if(c.reverteffect)d.reverteffect=c.reverteffect;else if(c.ghosting)d.reverteffect=function(a){a.style.top=0;a.style.left=0};if(c.endeffect)d.endeffect=c.endeffect;if(c.zindex)d.zindex=c.zindex;var f={overlap:c.overlap,containment:c.containment,tree:c.tree,hoverclass:c.hoverclass,onHover:Sortable.onHover};var g={onHover:Sortable.onEmptyHover,overlap:c.overlap,containment:c.containment,hoverclass:c.hoverclass};Element.cleanWhitespace(b);c.draggables=[];c.droppables=[];if(c.dropOnEmpty||c.tree){Droppables.add(b,g);c.droppables.push(b)}(c.elements||this.findElements(b,c)||[]).each(function(e,i){var a=c.handles?$(c.handles[i]):(c.handle?$(e).select('.'+c.handle)[0]:e);c.draggables.push(new Draggable(e,Object.extend(d,{handle:a})));Droppables.add(e,f);if(c.tree)e.treeNode=b;c.droppables.push(e)});if(c.tree){(Sortable.findTreeElements(b,c)||[]).each(function(e){Droppables.add(e,g);e.treeNode=b;c.droppables.push(e)})}this.sortables[b.id]=c;Draggables.addObserver(new SortableObserver(b,c.onUpdate))},findElements:function(a,b){return Element.findChildren(a,b.only,b.tree?true:false,b.tag)},findTreeElements:function(a,b){return Element.findChildren(a,b.only,b.tree?true:false,b.treeTag)},onHover:function(a,b,c){if(Element.isParent(b,a))return;if(c>.33&&c<.66&&Sortable.options(b).tree){return}else if(c>0.5){Sortable.mark(b,'before');if(b.previousSibling!=a){var d=a.parentNode;a.style.visibility="hidden";b.parentNode.insertBefore(a,b);if(b.parentNode!=d)Sortable.options(d).onChange(a);Sortable.options(b.parentNode).onChange(a)}}else{Sortable.mark(b,'after');var e=b.nextSibling||null;if(e!=a){var d=a.parentNode;a.style.visibility="hidden";b.parentNode.insertBefore(a,e);if(b.parentNode!=d)Sortable.options(d).onChange(a);Sortable.options(b.parentNode).onChange(a)}}},onEmptyHover:function(a,b,c){var d=a.parentNode;var e=Sortable.options(b);if(!Element.isParent(b,a)){var f;var g=Sortable.findElements(b,{tag:e.tag,only:e.only});var h=null;if(g){var i=Element.offsetSize(b,e.overlap)*(1.0-c);for(f=0;f<g.length;f+=1){if(i-Element.offsetSize(g[f],e.overlap)>=0){i-=Element.offsetSize(g[f],e.overlap)}else if(i-(Element.offsetSize(g[f],e.overlap)/2)>=0){h=f+1<g.length?g[f+1]:null;break}else{h=g[f];break}}}b.insertBefore(a,h);Sortable.options(d).onChange(a);e.onChange(a)}},unmark:function(){if(Sortable._marker)Sortable._marker.hide()},mark:function(a,b){var c=Sortable.options(a.parentNode);if(c&&!c.ghosting)return;if(!Sortable._marker){Sortable._marker=($('dropmarker')||Element.extend(document.createElement('DIV'))).hide().addClassName('dropmarker').setStyle({position:'absolute'});document.getElementsByTagName("body").item(0).appendChild(Sortable._marker)}var d=Position.cumulativeOffset(a);Sortable._marker.setStyle({left:d[0]+'px',top:d[1]+'px'});if(b=='after')if(c.overlap=='horizontal')Sortable._marker.setStyle({left:(d[0]+a.clientWidth)+'px'});else Sortable._marker.setStyle({top:(d[1]+a.clientHeight)+'px'});Sortable._marker.show()},_tree:function(a,b,c){var d=Sortable.findElements(a,b)||[];for(var i=0;i<d.length;++i){var e=d[i].id.match(b.format);if(!e)continue;var f={id:encodeURIComponent(e?e[1]:null),element:a,parent:c,children:[],position:c.children.length,container:$(d[i]).down(b.treeTag)};if(f.container)this._tree(f.container,b,f);c.children.push(f)}return c},tree:function(a){a=$(a);var b=this.options(a);var c=Object.extend({tag:b.tag,treeTag:b.treeTag,only:b.only,name:a.id,format:b.format},arguments[1]||{});var d={id:null,parent:null,children:[],container:a,position:0};return Sortable._tree(a,c,d)},_constructIndex:function(a){var b='';do{if(a.id)b='['+a.position+']'+b}while((a=a.parent)!=null);return b},sequence:function(b){b=$(b);var c=Object.extend(this.options(b),arguments[1]||{});return $(this.findElements(b,c)||[]).map(function(a){return a.id.match(c.format)?a.id.match(c.format)[1]:''})},setSequence:function(b,c){b=$(b);var d=Object.extend(this.options(b),arguments[2]||{});var e={};this.findElements(b,d).each(function(n){if(n.id.match(d.format))e[n.id.match(d.format)[1]]=[n,n.parentNode];n.parentNode.removeChild(n)});c.each(function(a){var n=e[a];if(n){n[1].appendChild(n[0]);delete e[a]}})},serialize:function(b){b=$(b);var c=Object.extend(Sortable.options(b),arguments[1]||{});var d=encodeURIComponent((arguments[1]&&arguments[1].name)?arguments[1].name:b.id);if(c.tree){return Sortable.tree(b,arguments[1]).children.map(function(a){return[d+Sortable._constructIndex(a)+"[id]="+encodeURIComponent(a.id)].concat(a.children.map(arguments.callee))}).flatten().join('&')}else{return Sortable.sequence(b,arguments[1]).map(function(a){return d+"[]="+encodeURIComponent(a)}).join('&')}}};Element.isParent=function(a,b){if(!a.parentNode||a==b)return false;if(a.parentNode==b)return true;return Element.isParent(a.parentNode,b)};Element.findChildren=function(b,c,d,f){if(!b.hasChildNodes())return null;f=f.toUpperCase();if(c)c=[c].flatten();var g=[];$A(b.childNodes).each(function(e){if(e.tagName&&e.tagName.toUpperCase()==f&&(!c||(Element.classNames(e).detect(function(v){return c.include(v)}))))g.push(e);if(d){var a=Element.findChildren(e,c,d,f);if(a)g.push(a)}});return(g.length>0?g.flatten():[])};Element.offsetSize=function(a,b){return a['offset'+((b=='vertical'||b=='height')?'Height':'Width')]};