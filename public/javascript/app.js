

      let mov=document.querySelectorAll('.slide-container');
    let i=0;
function rightPointer(){
mov[i].classList.remove("active");
i=(i+1)%mov.length;
mov[i].classList.add("active");
}
function leftPointer(){
mov[i].classList.remove("active");
i=(i - 1 + mov.length)%mov.length;
mov[i].classList.add("active");
}

let tog=document.querySelector('.toggle-menu');
let nav=document.querySelector('.navigation');
tog.addEventListener('click',function(){
tog.classList.toggle('active');   
nav.classList.toggle('active')                              
})

function seta(e){
document.getElementById('menu').classList.toggle('active');

}

const slide=document.querySelector(".slider").children;
const left=document.querySelector(".lef");
const right=document.querySelector(".rig");
const indicator=document.querySelector(".indicator");
let currentIndex=0;

left.addEventListener('click',function(){
moveLeft(); 
updateCount()                                  
})
right.addEventListener('click',function(){
moveRight();  
updateCount()                                 
})

function indicateDot(){
for(let i=0; i<slide.length; i++){
const div =document.createElement("div");   
div.innerHTML=i+1;
div.setAttribute('Oncllck',"circle(this)");
div.id=i;
if(i==0){
div.className="active-one";                                      
}
indicator.append(div);                              
}                                     
}
function updateCount(){
for(let i=0; i<indicator.children.length; i++){
indicator.children[i].classList.remove("active-one");
}            
indicator.children[currentIndex].classList.add("active-one");        
}
function circle(element){
currentIndex=element.id;
updateCount();
SlideShow();
}
indicateDot();
function moveRight(){
if(currentIndex==0){
currentIndex=slide.length-1;                                   
} else{
currentIndex--;  
reset();                                 
} 
SlideShow();                          
}
function moveLeft(){
if(currentIndex==slide.length-1){
currentIndex=0;                                   
} else{
currentIndex++;                                   
} 
SlideShow();  
reset();                        
}
function SlideShow(){
for(let i=0; i<slide.length; i++){
slide[i].classList.remove("active-one");
}
slide[currentIndex].classList.add("active-one");                       
}
function reset(){
clearInterval(timer); 
timer=setInterval(autoPlay,9000);                                      
}
function autoPlay(){
moveLeft();
updateCount();
rightPointer();
leftPointer();
}
let timer=setInterval(autoPlay,9000);

