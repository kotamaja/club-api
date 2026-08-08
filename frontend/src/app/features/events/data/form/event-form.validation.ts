import {EventFormBase, EventFormValidationError} from './event-form.model';

/**
 * Validates the shared event form fields.
 */
export function validateEventForm(form: EventFormBase): EventFormValidationError[] {
  const errors: EventFormValidationError[] = [];

  if (form.title.trim() === '') {
    errors.push({
      field: 'title',
      message: 'Le titre est obligatoire.',
    });
  }

  if (form.startsAt === '') {
    errors.push({
      field: 'startsAt',
      message: 'La date de début est obligatoire.',
    });
  }

  if (form.endsAt === '') {
    errors.push({
      field: 'endsAt',
      message: 'La date de fin est obligatoire.',
    });
  }

  if (form.startsAt !== '' && form.endsAt !== '' && form.endsAt <= form.startsAt) {
    errors.push({
      field: 'endsAt',
      message: 'La date de fin doit être postérieure à la date de début.',
    });
  }

  if (form.capacity.trim() !== '' && !/^\d+$/.test(form.capacity.trim())) {
    errors.push({
      field: 'capacity',
      message: 'La capacité doit être vide ou être un entier supérieur ou égal à 0.',
    });
  }

  if (form.registrationStartsAt !== '' && form.registrationEndsAt !== '' && form.registrationEndsAt <= form.registrationStartsAt) {
    errors.push({
      field: 'registrationEndsAt',
      message: 'La fin des inscriptions doit être postérieure au début des inscriptions.',
    });
  }

  return errors;
}
