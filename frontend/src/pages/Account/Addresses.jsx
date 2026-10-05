import { useId, useState } from 'react';
import Icon from '../../components/ui/Icon.jsx';
import { delivery } from '../../data/content.js';
import { AccountCard, BriefcaseIcon } from './AccountParts.jsx';

const zoneName = (id) => delivery.zones.find((z) => z.id === id)?.name ?? '';
const LABELS = ['Home', 'Office', 'Other'];
const NEW_ID = 'new';

function validPhone(v) {
  // Saved numbers are shown masked (017•••••678); keep them as-is unless edited.
  if (v.includes('•')) return true;
  return /^(?:\+?88)?01[3-9]\d{8}$/.test(v.replace(/[\s-]/g, ''));
}

function LabelIcon({ label, isDefault }) {
  const cls = `acct-address__icon${isDefault ? ' is-default' : ''}`;
  if (label === 'Home') return <Icon name="home" size={18} className={cls} />;
  if (label === 'Office') return <span className={cls}><BriefcaseIcon /></span>;
  return <Icon name="map-pin" size={18} className={cls} />;
}

/**
 * Saved addresses: cards with Edit / Set as default / Delete and an
 * "Add new address" tile that opens an inline form.
 * State lives in Account (list + setList) so it survives tab switches.
 */
export default function Addresses({ list, setList, as, onNotice }) {
  const [editing, setEditing] = useState(null); // address id | 'new' | null

  const save = (input) => {
    const address = input.id === NEW_ID ? { ...input, id: `addr-${Date.now()}` } : input;
    setList((prev) => {
      const exists = prev.some((a) => a.id === address.id);
      let next = exists ? prev.map((a) => (a.id === address.id ? address : a)) : [...prev, address];
      if (address.isDefault) next = next.map((a) => ({ ...a, isDefault: a.id === address.id }));
      if (!next.some((a) => a.isDefault) && next[0]) next[0] = { ...next[0], isDefault: true };
      return next;
    });
    setEditing(null);
    onNotice(`${address.label} address saved.`);
  };

  const setDefault = (id) => {
    setList((prev) => prev.map((a) => ({ ...a, isDefault: a.id === id })));
    onNotice('Default address updated.');
  };

  const remove = (address) => {
    const snapshot = list;
    setList((prev) => {
      const next = prev.filter((a) => a.id !== address.id);
      if (address.isDefault && next[0]) next[0] = { ...next[0], isDefault: true };
      return next;
    });
    onNotice(`${address.label} address deleted.`, { label: 'Undo', onClick: () => setList(snapshot) });
  };

  return (
    <AccountCard id="addresses" title="Saved addresses" as={as}>
      <div className="acct-addresses">
        {list.map((a) =>
          editing === a.id ? (
            <AddressForm key={a.id} initial={a} onSave={save} onCancel={() => setEditing(null)} />
          ) : (
            <article key={a.id} className={`acct-address${a.isDefault ? ' is-default' : ''}`} aria-label={`${a.label} address`}>
              <div className="acct-address__head">
                <span className="acct-address__label">
                  <LabelIcon label={a.label} isDefault={a.isDefault} />
                  {a.label}
                </span>
                {a.isDefault && <span className="status-chip status-chip--delivered">Default</span>}
              </div>
              <p className="acct-address__text">
                {a.name} · <span className="tabular">{a.phone}</span>
                {a.lines.map((line, i) => (
                  <span key={i}>
                    <br />
                    {line}
                    {i === a.lines.length - 1 && ` · ${zoneName(a.zone)}`}
                  </span>
                ))}
              </p>
              <div className="acct-address__actions">
                <button type="button" className="acct-textbtn" onClick={() => setEditing(a.id)} aria-label={`Edit ${a.label} address`}>
                  Edit
                </button>
                {!a.isDefault && (
                  <button type="button" className="acct-textbtn acct-textbtn--ink" onClick={() => setDefault(a.id)}>
                    Set as default
                  </button>
                )}
                <button
                  type="button"
                  className="acct-textbtn acct-textbtn--danger"
                  onClick={() => remove(a)}
                  aria-label={`Delete ${a.label} address`}
                >
                  Delete
                </button>
              </div>
            </article>
          ),
        )}

        {editing === 'new' ? (
          <AddressForm
            initial={{ id: NEW_ID, label: 'Home', isDefault: list.length === 0, name: '', phone: '', lines: ['', ''], zone: delivery.defaultZone }}
            isNew
            onSave={save}
            onCancel={() => setEditing(null)}
          />
        ) : (
          <button type="button" className="acct-address-add" onClick={() => setEditing('new')}>
            <span className="acct-address-add__icon">
              <Icon name="plus" size={20} strokeWidth={2} />
            </span>
            <span className="acct-address-add__title">Add new address</span>
            <span className="acct-address-add__text">Home, office or a gift address</span>
          </button>
        )}
      </div>
    </AccountCard>
  );
}

function AddressForm({ initial, isNew = false, onSave, onCancel }) {
  const uid = useId();
  const [v, setV] = useState({
    ...initial,
    line1: initial.lines[0] ?? '',
    line2: initial.lines[1] ?? '',
  });
  const [errors, setErrors] = useState({});
  const set = (key) => (e) => {
    const value = e.target.type === 'checkbox' ? e.target.checked : e.target.value;
    setV((s) => ({ ...s, [key]: value }));
  };

  const submit = (e) => {
    e.preventDefault();
    const next = {};
    if (!v.name.trim()) next.name = 'Enter the recipient’s name.';
    if (!validPhone(v.phone)) next.phone = 'Enter a valid mobile number, e.g. 01712-345678.';
    if (!v.line1.trim()) next.line1 = 'Enter house, road and area.';
    if (!v.line2.trim()) next.line2 = 'Enter the city and postcode.';
    setErrors(next);
    const first = Object.keys(next)[0];
    if (first) {
      document.getElementById(`${uid}-${first}`)?.focus();
      return;
    }
    const { line1, line2, ...rest } = v;
    onSave({ ...rest, name: v.name.trim(), lines: [line1.trim(), line2.trim()] });
  };

  const field = (key, label, props = {}) => (
    <div className="field">
      <label className="label" htmlFor={`${uid}-${key}`}>
        {label}
      </label>
      <input
        id={`${uid}-${key}`}
        className="input acct-input"
        value={v[key]}
        onChange={set(key)}
        aria-invalid={Boolean(errors[key]) || undefined}
        aria-describedby={errors[key] ? `${uid}-${key}-err` : undefined}
        {...props}
      />
      {errors[key] && (
        <span id={`${uid}-${key}-err`} className="error-text">
          {errors[key]}
        </span>
      )}
    </div>
  );

  return (
    <form className="acct-address-form" onSubmit={submit} noValidate aria-label={isNew ? 'New address' : `Edit ${initial.label} address`}>
      <fieldset className="acct-address-form__labels">
        <legend className="label">Label</legend>
        <div className="acct-address-form__chips">
          {LABELS.map((l) => (
            <label key={l} className={`acct-radio-chip${v.label === l ? ' is-selected' : ''}`}>
              <input type="radio" name={`${uid}-label`} value={l} checked={v.label === l} onChange={set('label')} />
              {l}
            </label>
          ))}
        </div>
      </fieldset>
      {field('name', 'Full name', { autoComplete: 'name' })}
      {field('phone', 'Mobile number', { type: 'tel', autoComplete: 'tel', placeholder: '01XXX-XXXXXX' })}
      {field('line1', 'Address', { autoComplete: 'address-line1', placeholder: 'House, road, block, area' })}
      {field('line2', 'City & postcode', { autoComplete: 'address-line2', placeholder: 'Dhaka 1216' })}
      <div className="field">
        <label className="label" htmlFor={`${uid}-zone`}>
          Delivery zone
        </label>
        <select id={`${uid}-zone`} className="select acct-input" value={v.zone} onChange={set('zone')}>
          {delivery.zones.map((z) => (
            <option key={z.id} value={z.id}>
              {z.name} · ৳{z.fee}
            </option>
          ))}
        </select>
      </div>
      <label className="acct-check">
        <input type="checkbox" className="checkbox" checked={v.isDefault} onChange={set('isDefault')} />
        Use as my default address
      </label>
      <div className="acct-address-form__actions">
        <button type="submit" className="btn btn--brand btn--sm">
          Save address
        </button>
        <button type="button" className="btn btn--outline btn--sm" onClick={onCancel}>
          Cancel
        </button>
      </div>
    </form>
  );
}
