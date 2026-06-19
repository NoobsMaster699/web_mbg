(function(){
 const sidebar=document.querySelector('.sidebar');
 const toastContainer=document.getElementById('toast-container');
 const confirmModal=document.querySelector('[data-confirm-modal]');
 const confirmBackdrop=document.querySelector('[data-confirm-backdrop]');
 const confirmMessage=document.querySelector('[data-confirm-message]');
 const confirmAccept=document.querySelector('[data-confirm-accept]');
 const confirmCancel=document.querySelector('[data-confirm-cancel]');
 let pendingForm=null,lastTrigger=null,activeAppModal=null,lastModalTrigger=null;
 const icons={success:'✓',error:'!',warning:'!',info:'i'};
 const titles={success:'Berhasil',error:'Gagal',warning:'Peringatan',info:'Informasi'};
 function showToast(type,title,message){
  if(!toastContainer) return;
  const safeType=['success','error','warning','info'].includes(type)?type:'info';
  const toast=document.createElement('div');
  toast.className=`toast toast-${safeType}`;
  toast.setAttribute('role','status');
  toast.innerHTML=`<span class="alert-icon" aria-hidden="true">${icons[safeType]}</span><div><strong></strong><p></p></div><button class="alert-close" type="button" aria-label="Tutup toast">&times;</button>`;
  toast.querySelector('strong').textContent=title||titles[safeType];
  toast.querySelector('p').textContent=message||'';
  toast.querySelector('button').addEventListener('click',()=>toast.remove());
  toastContainer.appendChild(toast);
  setTimeout(()=>toast.remove(),5200);
 }
 function setLoading(form){
  const submitter=form.querySelector('[type="submit"]');
  if(!submitter || submitter.classList.contains('is-loading')) return;
  submitter.dataset.originalText=submitter.textContent.trim();
  submitter.textContent=form.dataset.loadingText||submitter.dataset.loadingText||'Memproses...';
  submitter.classList.add('is-loading');
  submitter.disabled=true;
 }
 function openConfirm(form){
  if(!confirmModal||!confirmBackdrop){setLoading(form);form.submit();return;}
  pendingForm=form;lastTrigger=document.activeElement;
  confirmMessage.textContent=form.dataset.confirm||'Data yang sudah diproses mungkin akan berubah. Apakah Anda yakin ingin melanjutkan?';
  confirmModal.hidden=false;confirmBackdrop.hidden=false;
  setTimeout(()=>confirmCancel&&confirmCancel.focus(),0);
 }
 function closeConfirm(){
  if(confirmModal) confirmModal.hidden=true;
  if(confirmBackdrop) confirmBackdrop.hidden=true;
  pendingForm=null;
  if(lastTrigger&&lastTrigger.focus) lastTrigger.focus();
 }
 function appModalElements(id){
  return {
   modal:document.querySelector(`[data-modal="${id}"]`),
   backdrop:document.querySelector(`[data-modal-backdrop="${id}"]`)
  };
 }
 function openAppModal(id,trigger){
  const {modal,backdrop}=appModalElements(id);
  if(!modal) return;
  activeAppModal=modal;
  lastModalTrigger=trigger||document.activeElement;
  modal.hidden=false;
  if(backdrop) backdrop.hidden=false;
  setTimeout(()=>modal.querySelector('input,select,textarea,button')?.focus(),0);
 }
 function closeAppModal(id){
  const {modal,backdrop}=appModalElements(id);
  if(!modal) return;
  modal.hidden=true;
  if(backdrop) backdrop.hidden=true;
  activeAppModal=null;
  if(lastModalTrigger&&lastModalTrigger.focus) lastModalTrigger.focus();
 }
 document.querySelectorAll('[data-toggle="sidebar"]').forEach(btn=>btn.addEventListener('click',()=>sidebar&&sidebar.classList.toggle('open')));
 document.querySelectorAll('[data-demo-alert]').forEach(btn=>btn.addEventListener('click',()=>showToast('info','Fitur Placeholder','Fitur ini siap diintegrasikan ke controller PHP.')));
 document.querySelectorAll('.side-menu a, .nav-links a').forEach(a=>{ if(a.getAttribute('href')===location.pathname) a.classList.add('active')});
 document.querySelectorAll('[data-alert-close]').forEach(btn=>btn.addEventListener('click',()=>btn.closest('[data-alert]')?.remove()));
 document.querySelectorAll('[data-modal-open]').forEach(btn=>btn.addEventListener('click',()=>openAppModal(btn.dataset.modalOpen,btn)));
 document.querySelectorAll('[data-modal-close]').forEach(btn=>btn.addEventListener('click',()=>closeAppModal(btn.dataset.modalClose)));
 document.querySelectorAll('[data-modal-backdrop]').forEach(backdrop=>backdrop.addEventListener('click',()=>closeAppModal(backdrop.dataset.modalBackdrop)));
 document.querySelectorAll('[data-modal-auto-open]').forEach(modal=>openAppModal(modal.dataset.modal));
 document.querySelectorAll('[data-loading-link]').forEach(link=>link.addEventListener('click',()=>{
  link.dataset.originalText=link.textContent.trim();
  link.textContent='Menyiapkan laporan...';
  link.classList.add('is-loading');
 }));
 const flash=document.getElementById('flash-message-json');
 if(flash&&!document.querySelector('[data-alert]')){try{const data=JSON.parse(flash.textContent||'{}');showToast(data.type,data.title,data.message)}catch(e){}}
 document.addEventListener('submit',event=>{
  const form=event.target;
  if(!(form instanceof HTMLFormElement)) return;
  if(!form.checkValidity()) return;
  if(form.dataset.confirm && !form.dataset.confirmAccepted){event.preventDefault();openConfirm(form);return;}
  setLoading(form);
 });
 confirmAccept&&confirmAccept.addEventListener('click',()=>{if(!pendingForm)return;pendingForm.dataset.confirmAccepted='1';setLoading(pendingForm);pendingForm.submit();closeConfirm();});
 confirmCancel&&confirmCancel.addEventListener('click',closeConfirm);
 confirmBackdrop&&confirmBackdrop.addEventListener('click',closeConfirm);
 document.addEventListener('keydown',event=>{
  if(event.key!=='Escape') return;
  if(confirmModal&&!confirmModal.hidden){closeConfirm();return;}
  if(activeAppModal) closeAppModal(activeAppModal.dataset.modal);
 });
 function setScoreError(input,message,show){
  const error=input.parentElement?.querySelector('[data-input-error]');
  input.classList.toggle('input-error',show);
  input.classList.toggle('spk-input-error',show);
  input.setAttribute('aria-invalid',show?'true':'false');
  input.setCustomValidity(message||'');
  if(error){
   error.textContent=message||'Nilai harus berada pada rentang 0.000 sampai 1.000.';
   error.hidden=!show;
  }
 }
 function scoreState(input){
  const value=input.value.trim();
  if(value===''){
   setScoreError(input,'Nilai wajib diisi.',false);
   input.setCustomValidity('Nilai wajib diisi.');
   return {state:'empty',value:0};
  }
  const numeric=!Number.isNaN(Number(value));
  const formatOk=/^\d+(?:\.\d{1,3})?$/.test(value);
  const rangeOk=numeric&&Number(value)>=0&&Number(value)<=1;
  if(!numeric||!rangeOk||!formatOk){
   setScoreError(input,'Nilai harus berada pada rentang 0.000 sampai 1.000.',true);
   return {state:'invalid',value:0};
  }
  setScoreError(input,'',false);
  return {state:'valid',value:Number(value)};
 }
 function refreshPenilaianRow(row){
  const inputs=[...row.querySelectorAll('.score-input')];
  let hasEmpty=false,hasInvalid=false,hasPositive=false,sum=0,validCount=0;
  inputs.forEach(input=>{
   const result=scoreState(input);
   if(result.state==='empty') hasEmpty=true;
   if(result.state==='invalid') hasInvalid=true;
   if(result.state==='valid'){
    validCount++;
    sum+=result.value;
    if(result.value>0) hasPositive=true;
   }
  });
  let status='valid';
  if(hasInvalid) status='invalid';
  else if(hasEmpty||!hasPositive||validCount<inputs.length) status='incomplete';
  const average=row.querySelector('[data-row-average]');
  if(average) average.textContent=validCount===inputs.length&&!hasInvalid?(sum/Math.max(inputs.length,1)).toFixed(3):'-';
  const badge=row.querySelector('[data-row-status]');
  if(badge){
   const meta={
    valid:['spk-badge-valid','Valid'],
    incomplete:['spk-badge-warning','Belum lengkap'],
    invalid:['spk-badge-error','Tidak valid']
   }[status];
   badge.className=`spk-row-badge ${meta[0]}`;
   badge.textContent=meta[1];
  }
  const rowError=row.querySelector('[data-row-error]');
  if(rowError) rowError.hidden=!(status==='incomplete'&&!hasEmpty&&!hasInvalid&&!hasPositive);
  return status;
 }
 function refreshPenilaianForm(form){
  const rows=[...form.querySelectorAll('[data-penilaian-row]')];
  const counts={valid:0,incomplete:0,invalid:0};
  rows.forEach(row=>{counts[refreshPenilaianRow(row)]++;});
  const submit=form.querySelector('[type="submit"]');
  const ready=rows.length>0&&counts.valid===rows.length;
  if(submit){submit.disabled=!ready;submit.classList.toggle('is-disabled',!ready)}
  const status=form.querySelector('[data-validation-status]');
  if(status){
   const type=counts.invalid>0?'error':(counts.incomplete>0?'warning':'success');
   const message=type==='success'
    ?'Semua baris valid. Penilaian siap disimpan dan digunakan untuk proses SAW.'
    :(type==='error'
      ?'Terdapat nilai tidak valid. Periksa kembali nilai penilaian.'
      :'Masih ada menu yang belum lengkap dinilai. Lengkapi nilai C1-C5 sebelum menyimpan.');
   status.className=`spk-alert alert alert-${type} validation-status`;
   const icon=status.querySelector('.alert-icon');
   if(icon) icon.textContent=type==='success'?'✓':'!';
   status.querySelector('p').textContent=message;
  }
  const total=rows.length;
  const progress=total>0?Math.round((counts.valid/total)*100):0;
  const map={
   '[data-summary-valid]':counts.valid,
   '[data-summary-incomplete]':counts.incomplete,
   '[data-summary-invalid]':counts.invalid,
   '[data-summary-progress]':progress
  };
  Object.entries(map).forEach(([selector,value])=>document.querySelectorAll(selector).forEach(el=>{el.textContent=value;}));
 }
 document.querySelectorAll('[data-validate-penilaian]').forEach(form=>{
  refreshPenilaianForm(form);
  form.addEventListener('input',event=>{if(event.target.matches('.score-input'))refreshPenilaianForm(form);});
  form.addEventListener('blur',event=>{
   if(!event.target.matches('.score-input')) return;
   const result=scoreState(event.target);
   if(result.state==='valid') event.target.value=result.value.toFixed(3);
   refreshPenilaianForm(form);
  },true);
 });
 const saatyValues=['0.111111','0.125000','0.142857','0.166667','0.200000','0.250000','0.333333','0.500000','1.000000','2.000000','3.000000','4.000000','5.000000','6.000000','7.000000','8.000000','9.000000'];
 const nearestSaatyValue=value=>{
  const numeric=Number(value);
  let nearest=saatyValues[8],distance=Infinity;
  saatyValues.forEach(candidate=>{
   const diff=Math.abs(Number(candidate)-numeric);
   if(diff<distance){distance=diff;nearest=candidate;}
  });
  return nearest;
 };
 const isSaaty=value=>saatyValues.some(v=>Math.abs(Number(value)-Number(v))<0.00001);
 document.querySelectorAll('.ahp-cell').forEach(input=>input.addEventListener('change',()=>{
  const row=input.dataset.row,col=input.dataset.col,value=parseFloat(input.value);
  const valid=Number.isFinite(value)&&value>0&&isSaaty(value);
  input.classList.toggle('input-error',!valid);
  input.setCustomValidity(valid?'':'Gunakan skala Saaty 1-9 atau reciprocal 1/2 sampai 1/9.');
  if(!row||!col||row===col||!valid) return;
  const reciprocal=document.querySelector(`.ahp-cell[data-row="${col}"][data-col="${row}"]`);
  if(reciprocal) reciprocal.value=nearestSaatyValue(1/value);
 }));
 function applyAhpGuide(left,right){
  const direction=document.querySelector(`.ahp-guide-direction[data-left="${left}"][data-right="${right}"]`)?.value||'same';
  const intensity=Number(document.querySelector(`.ahp-guide-intensity[data-left="${left}"][data-right="${right}"]`)?.value||1);
  const safeIntensity=Math.min(9,Math.max(1,intensity||1));
  const leftCell=document.querySelector(`.ahp-cell[data-row="${left}"][data-col="${right}"]`);
  const rightCell=document.querySelector(`.ahp-cell[data-row="${right}"][data-col="${left}"]`);
  let leftValue='1.000000',rightValue='1.000000';
  if(direction==='left'){
   leftValue=nearestSaatyValue(safeIntensity);
   rightValue=nearestSaatyValue(1/safeIntensity);
  }else if(direction==='right'){
   leftValue=nearestSaatyValue(1/safeIntensity);
   rightValue=nearestSaatyValue(safeIntensity);
  }
  if(leftCell) leftCell.value=leftValue;
  if(rightCell) rightCell.value=rightValue;
 }
 document.querySelectorAll('.ahp-guide-direction,.ahp-guide-intensity').forEach(select=>select.addEventListener('change',()=>{
  applyAhpGuide(select.dataset.left,select.dataset.right);
 }));
})();

