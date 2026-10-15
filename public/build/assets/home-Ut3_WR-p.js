const c=document.getElementById("btnQueueAdd"),l=document.getElementById("btnQueueRemove");c&&c.addEventListener("click",t=>{axios.post("/api/v1/dart/queue/add").then(s=>{const e=document.getElementById("dartQueueList");if(!e)return;const n=document.createElement("div");n.className="queue-item",n.innerHTML=`
                <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-500 font-semibold text-sm">
                    ${e.children.length+1}
                </div>
                <div class="flex-1">
                    <span class="font-medium text-base-content">{{ __('me') }}</span>
                    <span class="text-base-content/50 text-sm ml-2">gerade eben</span>
                </div>
                <button id="btnQueueRemove" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;const i=e.querySelector(".text-center.py-8");i&&i.remove(),e.appendChild(n),document.querySelectorAll(".status-badge").forEach(r=>{r.classList.contains("danger")&&(r.classList.remove("danger","badge-error"),r.classList.add("success","badge-success"),r.innerHTML='<i class="fa-solid fa-check text-xs"></i> in queue')}),c.disabled=!0,c.classList.add("opacity-50","cursor-not-allowed");const a=n.querySelector("#btnQueueRemove");a&&d(a)}).catch(function(s){s.response&&console.log(s.response.data)})});l&&d(l);function d(t){t&&t.addEventListener("click",s=>{axios.post("/api/v1/dart/queue/remove").then(e=>{const n=s.target.closest(".queue-item")||s.target.closest("li");n&&n.remove(),document.querySelectorAll(".status-badge").forEach(a=>{a.classList.contains("success")&&(a.classList.remove("success","badge-success"),a.classList.add("danger","badge-error"),a.innerHTML='<i class="fa-solid fa-xmark text-xs"></i> not in queue')}),c&&(c.disabled=!1,c.classList.remove("opacity-50","cursor-not-allowed"));const o=document.getElementById("dartQueueList");o&&o.querySelectorAll(".queue-item").length===0&&(o.innerHTML=`
                    <div class="text-center py-8">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-base-200 flex items-center justify-center">
                            <i class="fa-solid fa-inbox text-xl text-base-content/50"></i>
                        </div>
                        <p class="text-base-content/50 text-sm">queue is empty</p>
                    </div>
                `)}).catch(function(e){e.response&&console.log(e.response.data)})})}const u=document.getElementById("BtnShare");u&&u.addEventListener("click",t=>{navigator.share&&navigator.share({title:"BlubberLounge Tools",url:window.location.origin}).then(()=>{console.log("Thanks for sharing!")}).catch(console.error)});m("/api/v1/dart/showThrows/9a42af70-31d9-4152-a851-73cb15f9ca58/user/1",t=>{f(dataGame,currentUser)});async function m(t,s){axios.get(t).then(s).catch(function(e){e.response&&(console.log(e.response.data),console.error("Data could not get fetched!"))})}function f(t){const s=document.getElementById("gameTypesGraph");var e=echarts.init(s,null,chartOptions);let n=[];for(const o of Object.keys(t))n.push({value:t[o],name:`${o}`});let i={title:{text:"Spieltypen %",left:10,top:10,textStyle:{color:"#ccc"}},tooltip:{trigger:"item"},series:[{name:"Spieltypen",type:"pie",radius:["35%","60%"],avoidLabelOverlap:!1,itemStyle:{borderRadius:9,borderColor:"rgba(43, 48, 53, 1)",borderWidth:8},label:{show:!0,color:"rgba(255, 255, 255, 0.3)",formatter(o){return o.name+" ("+o.percent+"%)"}},labelLine:{lineStyle:{color:"rgba(255, 255, 255, 0.3)"},smooth:.2,length:10,length2:20},data:n}]};e.setOption(i)}
