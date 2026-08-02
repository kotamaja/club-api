import {Capability} from '../capabilities/capability.model';
import {ClubSummary} from './club-summary.model';
import {OrganizationSummary} from './organization-summary.model';

export type CurrentContext = {
  organization: OrganizationSummary;
  club: ClubSummary;
  capabilities: Capability[];
};

export type CurrentContextState = |
  { status: 'unknown' } |
  { status: 'loading' } |
  { status: 'resolved'; context: CurrentContext } |
  { status: 'needs-organization-selection'; organizations: OrganizationSummary[] } |
  { status: 'needs-club-selection'; clubs: ClubSummary[] } |
  { status: 'no-access' } |
  { status: 'error'; message: string };
